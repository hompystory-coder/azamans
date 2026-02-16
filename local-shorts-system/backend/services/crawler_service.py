#!/usr/bin/env python3
"""
🕷️ 웹 크롤러 서비스
제품 페이지에서 정보 자동 추출
"""

from playwright.async_api import async_playwright, Page
from bs4 import BeautifulSoup
from pathlib import Path
from typing import Optional, Dict, List
from loguru import logger
import asyncio
import re

class CrawlerService:
    """웹 크롤링 서비스"""
    
    def __init__(self, headless: bool = True):
        self.headless = headless
        self.browser = None
        self.playwright = None
    
    async def __aenter__(self):
        """컨텍스트 매니저 진입"""
        self.playwright = await async_playwright().start()
        self.browser = await self.playwright.chromium.launch(headless=self.headless)
        return self
    
    async def __aexit__(self, exc_type, exc_val, exc_tb):
        """컨텍스트 매니저 종료"""
        if self.browser:
            await self.browser.close()
        if self.playwright:
            await self.playwright.stop()
    
    async def crawl_product(self, url: str) -> Dict:
        """제품 페이지 크롤링
        
        Args:
            url: 제품 페이지 URL
            
        Returns:
            제품 정보 딕셔너리
        """
        try:
            logger.info(f"🕷️ Crawling: {url}")
            
            # 페이지 로드
            page = await self.browser.new_page()
            
            try:
                await page.goto(url, wait_until="networkidle", timeout=30000)
                await asyncio.sleep(2)  # 동적 콘텐츠 로드 대기
                
                # HTML 가져오기
                html = await page.content()
                
                # 스크린샷 (디버깅용)
                # await page.screenshot(path="screenshot.png")
                
            finally:
                await page.close()
            
            # BeautifulSoup으로 파싱
            soup = BeautifulSoup(html, 'html.parser')
            
            # 제품 정보 추출
            product_info = self._extract_product_info(soup, url)
            
            logger.info(f"✅ Crawled: {product_info.get('title', 'Unknown')}")
            
            return product_info
            
        except Exception as e:
            logger.error(f"❌ Crawling failed: {str(e)}")
            # 폴백: 기본 정보 반환
            return {
                "title": "제품",
                "description": "크롤링 실패",
                "features": [],
                "price": "정보 없음",
                "url": url,
                "images": []
            }
    
    def _extract_product_info(self, soup: BeautifulSoup, url: str) -> Dict:
        """HTML에서 제품 정보 추출"""
        
        # URL 기반 사이트 감지
        if "naver.com" in url:
            return self._extract_naver(soup, url)
        elif "coupang.com" in url:
            return self._extract_coupang(soup, url)
        elif "11st.co.kr" in url:
            return self._extract_11st(soup, url)
        else:
            return self._extract_generic(soup, url)
    
    def _extract_naver(self, soup: BeautifulSoup, url: str) -> Dict:
        """네이버 쇼핑/블로그 크롤링"""
        
        # 제목
        title = ""
        title_elem = soup.select_one("h1, .se-title, .post_title, .product_title")
        if title_elem:
            title = title_elem.get_text(strip=True)
        
        # 설명
        description = ""
        desc_elem = soup.select_one(".se-text, .post_ct, .product_description")
        if desc_elem:
            description = desc_elem.get_text(strip=True)[:500]
        
        # 가격
        price = ""
        price_elem = soup.select_one(".price, .product_price, span[class*='price']")
        if price_elem:
            price = price_elem.get_text(strip=True)
        
        # 이미지
        images = []
        img_elems = soup.select("img[src*='http']")
        for img in img_elems[:5]:  # 최대 5개
            src = img.get("src")
            if src and any(ext in src for ext in [".jpg", ".jpeg", ".png"]):
                images.append(src)
        
        # 특징 추출 (리스트)
        features = []
        list_elems = soup.select("ul li, ol li")
        for li in list_elems[:5]:
            text = li.get_text(strip=True)
            if text and len(text) < 100:
                features.append(text)
        
        return {
            "title": title or "제품",
            "description": description or "설명 없음",
            "features": features,
            "price": price,
            "url": url,
            "images": images,
            "source": "naver"
        }
    
    def _extract_coupang(self, soup: BeautifulSoup, url: str) -> Dict:
        """쿠팡 크롤링"""
        
        # 제목
        title = ""
        title_elem = soup.select_one(".prod-buy-header__title, h1")
        if title_elem:
            title = title_elem.get_text(strip=True)
        
        # 가격
        price = ""
        price_elem = soup.select_one(".total-price strong, .price-value")
        if price_elem:
            price = price_elem.get_text(strip=True) + "원"
        
        # 이미지
        images = []
        img_elems = soup.select("img.prod-image__detail")
        for img in img_elems[:5]:
            src = img.get("src") or img.get("data-src")
            if src:
                images.append(src)
        
        # 특징
        features = []
        feature_elems = soup.select(".prod-description__attribute li")
        for elem in feature_elems[:5]:
            features.append(elem.get_text(strip=True))
        
        return {
            "title": title or "제품",
            "description": "",
            "features": features,
            "price": price,
            "url": url,
            "images": images,
            "source": "coupang"
        }
    
    def _extract_11st(self, soup: BeautifulSoup, url: str) -> Dict:
        """11번가 크롤링"""
        
        title = ""
        title_elem = soup.select_one(".title, h1")
        if title_elem:
            title = title_elem.get_text(strip=True)
        
        price = ""
        price_elem = soup.select_one(".price, .sale_price")
        if price_elem:
            price = price_elem.get_text(strip=True)
        
        images = []
        img_elems = soup.select("img[src*='product']")
        for img in img_elems[:5]:
            src = img.get("src")
            if src:
                images.append(src)
        
        return {
            "title": title or "제품",
            "description": "",
            "features": [],
            "price": price,
            "url": url,
            "images": images,
            "source": "11st"
        }
    
    def _extract_generic(self, soup: BeautifulSoup, url: str) -> Dict:
        """일반 사이트 크롤링 (휴리스틱)"""
        
        # 제목 추출 시도
        title = ""
        for selector in ["h1", ".product-title", ".title", "[itemprop='name']"]:
            elem = soup.select_one(selector)
            if elem:
                title = elem.get_text(strip=True)
                break
        
        if not title:
            title_tag = soup.find("title")
            if title_tag:
                title = title_tag.get_text(strip=True)
        
        # 설명
        description = ""
        for selector in ["meta[name='description']", ".description", ".product-description"]:
            elem = soup.select_one(selector)
            if elem:
                description = elem.get("content", "") or elem.get_text(strip=True)
                break
        
        # 가격
        price = ""
        for selector in [".price", "[itemprop='price']", "span[class*='price']"]:
            elem = soup.select_one(selector)
            if elem:
                price = elem.get_text(strip=True)
                break
        
        # 이미지
        images = []
        img_elems = soup.select("img[src*='http']")
        for img in img_elems[:5]:
            src = img.get("src")
            if src and any(ext in src for ext in [".jpg", ".jpeg", ".png"]):
                images.append(src)
        
        # 특징 (리스트 아이템)
        features = []
        list_items = soup.select("ul li, ol li")
        for li in list_items[:5]:
            text = li.get_text(strip=True)
            if text and 10 < len(text) < 100:
                features.append(text)
        
        return {
            "title": title or "제품",
            "description": description[:500] if description else "",
            "features": features,
            "price": price,
            "url": url,
            "images": images,
            "source": "generic"
        }
    
    @staticmethod
    def clean_text(text: str) -> str:
        """텍스트 정리"""
        # 연속 공백 제거
        text = re.sub(r'\s+', ' ', text)
        # 특수문자 제거 (한글, 영문, 숫자, 기본 문장부호만)
        text = re.sub(r'[^\w\s\.\,\!\?\(\)\-\+\/\:]', '', text)
        return text.strip()


# ========== 간단한 동기 래퍼 ==========
class SimpleCrawler:
    """간단한 동기 크롤러 (Playwright 없이)"""
    
    @staticmethod
    def crawl_simple(url: str) -> Dict:
        """requests + BeautifulSoup 사용 (빠름)"""
        import requests
        
        try:
            logger.info(f"🕷️ Simple crawling: {url}")
            
            headers = {
                "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36"
            }
            
            response = requests.get(url, headers=headers, timeout=10)
            response.raise_for_status()
            
            soup = BeautifulSoup(response.text, 'html.parser')
            
            # 제목
            title = ""
            h1 = soup.find("h1")
            if h1:
                title = h1.get_text(strip=True)
            else:
                title_tag = soup.find("title")
                if title_tag:
                    title = title_tag.get_text(strip=True)
            
            # 설명
            meta_desc = soup.find("meta", {"name": "description"})
            description = meta_desc.get("content", "") if meta_desc else ""
            
            # 이미지
            images = []
            for img in soup.find_all("img", limit=5):
                src = img.get("src")
                if src and "http" in src:
                    images.append(src)
            
            logger.info(f"✅ Simple crawl: {title}")
            
            return {
                "title": title or "제품",
                "description": description,
                "features": [],
                "price": "",
                "url": url,
                "images": images,
                "source": "simple"
            }
            
        except Exception as e:
            logger.error(f"❌ Simple crawl failed: {str(e)}")
            return {
                "title": "제품",
                "description": "크롤링 실패",
                "features": [],
                "price": "",
                "url": url,
                "images": []
            }


# ========== 테스트 코드 ==========
if __name__ == "__main__":
    import asyncio
    
    async def test_crawl():
        test_url = "https://blog.naver.com/example"
        
        # Playwright 크롤러 테스트
        async with CrawlerService(headless=True) as crawler:
            result = await crawler.crawl_product(test_url)
            print(json.dumps(result, indent=2, ensure_ascii=False))
    
    # asyncio.run(test_crawl())
    
    # Simple 크롤러 테스트
    result = SimpleCrawler.crawl_simple("https://www.example.com")
    print(result)
