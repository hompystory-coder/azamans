"""
네이버 금융 실시간 주가 크롤러
진짜 실시간 데이터!
"""

import requests
from bs4 import BeautifulSoup
import re
from datetime import datetime

def get_naver_stock_price(code):
    """
    네이버 금융에서 실시간 주가 가져오기
    
    Args:
        code: 종목 코드 (예: "005930" for 삼성전자)
    
    Returns:
        dict: 주가 정보
    """
    
    # 종목 코드 정리 (005930.KS -> 005930)
    if '.' in code:
        code = code.split('.')[0]
    
    url = f"https://finance.naver.com/item/main.naver?code={code}"
    
    try:
        headers = {
            'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
        }
        response = requests.get(url, headers=headers, timeout=10)
        response.raise_for_status()
        
        soup = BeautifulSoup(response.text, 'html.parser')
        
        # 현재가
        price_element = soup.select_one('.rate_info .no_today .blind')
        if not price_element:
            return None
        
        current_price = int(price_element.text.replace(',', ''))
        
        # 전일 대비
        change_element = soup.select_one('.rate_info .no_exday .blind')
        prev_close_text = change_element.text if change_element else "0"
        prev_close = int(prev_close_text.replace(',', ''))
        
        # 등락
        change_price_element = soup.select_one('.rate_info .no_exday .blind')
        change_percent_element = soup.select_one('.rate_info .no_exday .blind')
        
        # 등락률
        change_text = soup.select_one('.rate_info .no_exday .blind')
        if change_text:
            change_str = change_text.text.replace(',', '').replace('+', '').replace('-', '')
            change_price = int(change_str) if change_str.isdigit() else 0
        else:
            change_price = 0
        
        # 등락 방향
        change_direction = "상승"
        if soup.select_one('.rate_info .no_exday.down3'):
            change_direction = "하락"
        elif soup.select_one('.rate_info .no_exday.up'):
            change_direction = "상승"
        
        # 시가, 고가, 저가, 거래량
        table = soup.select('.rate_info .tb_info table td')
        
        open_price = 0
        high_price = 0
        low_price = 0
        volume = 0
        
        for i, td in enumerate(table):
            text = td.text.strip().replace(',', '').replace('원', '').replace('주', '')
            try:
                if '시가' in td.text:
                    open_price = int(table[i+1].text.replace(',', ''))
                elif '고가' in td.text:
                    high_price = int(table[i+1].text.replace(',', ''))
                elif '저가' in td.text:
                    low_price = int(table[i+1].text.replace(',', ''))
                elif '거래량' in td.text:
                    volume_text = table[i+1].text.replace(',', '').replace('주', '')
                    volume = int(volume_text) if volume_text.isdigit() else 0
            except:
                pass
        
        # 회사명
        company_name = soup.select_one('.wrap_company h2 a')
        company_name = company_name.text if company_name else code
        
        return {
            'code': code,
            'name': company_name,
            'current_price': current_price,
            'prev_close': prev_close if prev_close > 0 else current_price,
            'change_price': change_price,
            'change_direction': change_direction,
            'change_percent': (change_price / prev_close * 100) if prev_close > 0 else 0,
            'open': open_price,
            'high': high_price,
            'low': low_price,
            'volume': volume,
            'timestamp': datetime.now().strftime('%Y-%m-%d %H:%M:%S'),
            'source': 'NAVER 금융 (실시간)'
        }
        
    except Exception as e:
        print(f"Error: {e}")
        return None


def format_price_display(data):
    """주가 정보를 보기 좋게 포맷"""
    
    if not data:
        return "데이터를 가져올 수 없습니다."
    
    arrow = "▲" if data['change_direction'] == "상승" else "▼" if data['change_direction'] == "하락" else "━"
    sign = "+" if data['change_direction'] == "상승" else "-" if data['change_direction'] == "하락" else ""
    
    result = f"""
╔══════════════════════════════════════════════════╗
║     📊 {data['name']} ({data['code']})
╚══════════════════════════════════════════════════╝

🔴 실시간 현재가

💰 {data['current_price']:,}원

{arrow} {sign}{data['change_price']:,}원 ({sign}{data['change_percent']:.2f}%)

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📊 오늘 시세

🔓 시가: {data['open']:,}원
⬆️  고가: {data['high']:,}원
⬇️  저가: {data['low']:,}원
📦 거래량: {data['volume']:,}주

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

📅 전일종가: {data['prev_close']:,}원

🕐 업데이트: {data['timestamp']}
📡 데이터 출처: {data['source']}

✅ 이 데이터는 실시간입니다!
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
"""
    
    return result


if __name__ == "__main__":
    # 테스트
    print("삼성전자 실시간 주가 확인 중...")
    data = get_naver_stock_price("005930")
    
    if data:
        print(format_price_display(data))
    else:
        print("데이터를 가져올 수 없습니다.")
