"""
한국 & 미국 주요 기업 500개 티커 및 검색어 매핑
"""

# 한국 주요 기업 (KOSPI/KOSDAQ 시가총액 상위 + 주요 기업)
KOREA_TOP_COMPANIES = {
    # 티커: (한글명, 영문명, 검색 키워드 리스트)
    
    # 대형주 (시총 TOP 100)
    '005930.KS': ('삼성전자', 'Samsung Electronics', ['삼성전자', '삼성', 'samsung']),
    '000660.KS': ('SK하이닉스', 'SK Hynix', ['sk하이닉스', 'SK하이닉스', 'skhynix', 'sk hynix']),
    '005490.KS': ('POSCO홀딩스', 'POSCO Holdings', ['posco', '포스코', '포스코홀딩스']),
    '005380.KS': ('현대차', 'Hyundai Motor', ['현대차', '현대자동차', '현대', 'hyundai']),
    '000270.KS': ('기아', 'Kia', ['기아', 'kia']),
    '068270.KS': ('셀트리온', 'Celltrion', ['셀트리온', 'celltrion']),
    '207940.KS': ('삼성바이오로직스', 'Samsung Biologics', ['삼성바이오로직스', '삼성바이오']),
    '035420.KS': ('NAVER', 'NAVER', ['네이버', 'naver']),
    '035720.KS': ('카카오', 'Kakao', ['카카오', 'kakao']),
    '051910.KS': ('LG화학', 'LG Chem', ['lg화학', 'LG화학', 'lgchem']),
    '006400.KS': ('삼성SDI', 'Samsung SDI', ['삼성sdi', '삼성SDI', 'samsungsdi']),
    '373220.KS': ('LG에너지솔루션', 'LG Energy Solution', ['lg에너지솔루션', 'LG에너지솔루션', 'lges']),
    '105560.KS': ('KB금융', 'KB Financial', ['kb금융', 'KB금융', 'kbfg']),
    '055550.KS': ('신한지주', 'Shinhan Financial', ['신한지주', '신한금융지주', '신한']),
    '086790.KS': ('하나금융지주', 'Hana Financial', ['하나금융지주', '하나금융', '하나']),
    '032830.KS': ('삼성생명', 'Samsung Life', ['삼성생명']),
    '012330.KS': ('현대모비스', 'Hyundai Mobis', ['현대모비스', 'mobis']),
    '028260.KS': ('삼성물산', 'Samsung C&T', ['삼성물산']),
    '034730.KS': ('SK', 'SK Inc', ['sk', 'SK']),
    '017670.KS': ('SK텔레콤', 'SK Telecom', ['sk텔레콤', 'SK텔레콤', 'skt']),
    '030200.KS': ('KT', 'KT', ['kt', 'KT']),
    '009150.KS': ('삼성전기', 'Samsung Electro-Mechanics', ['삼성전기']),
    '010130.KS': ('고려아연', 'Korea Zinc', ['고려아연']),
    '034020.KS': ('두산에너빌리티', 'Doosan Enerbility', ['두산에너빌리티', '두산', 'doosan']),
    '000810.KS': ('삼성화재', 'Samsung Fire', ['삼성화재']),
    '018260.KS': ('삼성에스디에스', 'Samsung SDS', ['삼성에스디에스', '삼성sds']),
    '003550.KS': ('LG', 'LG Corp', ['lg', 'LG']),
    '066570.KS': ('LG전자', 'LG Electronics', ['lg전자', 'LG전자']),
    '096770.KS': ('SK이노베이션', 'SK Innovation', ['sk이노베이션', 'SK이노베이션']),
    '015760.KS': ('한국전력', 'KEPCO', ['한국전력', '한전', 'kepco']),
    '011200.KS': ('HMM', 'HMM', ['hmm', 'HMM', '현대상선']),
    '003490.KS': ('대한항공', 'Korean Air', ['대한항공', 'koreanair']),
    '032640.KS': ('LG유플러스', 'LG Uplus', ['lg유플러스', 'LG유플러스', 'lgu+']),
    '010950.KS': ('S-Oil', 'S-Oil', ['s-oil', 'S-Oil', '에쓰오일']),
    '009540.KS': ('HD한국조선해양', 'HD Korea Shipbuilding', ['hd한국조선해양', '한국조선해양']),
    '011170.KS': ('롯데케미칼', 'Lotte Chemical', ['롯데케미칼']),
    '004020.KS': ('현대제철', 'Hyundai Steel', ['현대제철']),
    '138040.KS': ('메리츠금융지주', 'Meritz Financial', ['메리츠금융지주', '메리츠']),
    '071050.KS': ('한국금융지주', 'Korea Financial', ['한국금융지주']),
    '161390.KS': ('한국타이어앤테크놀로지', 'Hankook Tire', ['한국타이어', '한국타이어앤테크놀로지']),
    '000720.KS': ('현대건설', 'Hyundai E&C', ['현대건설']),
    '002380.KS': ('KCC', 'KCC', ['kcc', 'KCC']),
    '004170.KS': ('신세계', 'Shinsegae', ['신세계']),
    '001450.KS': ('현대해상', 'Hyundai Marine', ['현대해상']),
    '010140.KS': ('삼성중공업', 'Samsung Heavy', ['삼성중공업']),
    '011070.KS': ('LG이노텍', 'LG Innotek', ['lg이노텍', 'LG이노텍']),
    '036460.KS': ('한국가스공사', 'KOGAS', ['한국가스공사', '가스공사', 'kogas']),
    '028050.KS': ('삼성엔지니어링', 'Samsung Engineering', ['삼성엔지니어링']),
    '047810.KS': ('한국항공우주', 'KAI', ['한국항공우주', 'kai']),
    '001040.KS': ('CJ', 'CJ Corp', ['cj', 'CJ']),
    '097950.KS': ('CJ제일제당', 'CJ CheilJedang', ['cj제일제당', 'CJ제일제당']),
    '000100.KS': ('유한양행', 'Yuhan', ['유한양행']),
    '024110.KS': ('기업은행', 'IBK', ['기업은행', 'ibk']),
    '003670.KS': ('포스코퓨처엠', 'POSCO Future M', ['포스코퓨처엠']),
    '009830.KS': ('한화솔루션', 'Hanwha Solutions', ['한화솔루션', '한화']),
    '272210.KS': ('한화시스템', 'Hanwha Systems', ['한화시스템']),
    '012450.KS': ('한화에어로스페이스', 'Hanwha Aerospace', ['한화에어로스페이스']),
    '000150.KS': ('두산', 'Doosan', ['두산', 'doosan']),
    '241560.KS': ('두산밥캣', 'Doosan Bobcat', ['두산밥캣']),
    '004990.KS': ('롯데지주', 'Lotte', ['롯데지주', '롯데']),
    '023530.KS': ('롯데쇼핑', 'Lotte Shopping', ['롯데쇼핑']),
    '004000.KS': ('롯데정밀화학', 'Lotte Fine Chemical', ['롯데정밀화학']),
    '271560.KS': ('오리온', 'Orion', ['오리온', 'orion']),
    '000120.KS': ('CJ대한통운', 'CJ Logistics', ['cj대한통운', 'CJ대한통운']),
    '028670.KS': ('팬오션', 'Pan Ocean', ['팬오션']),
    '139480.KS': ('이마트', 'E-Mart', ['이마트', 'emart']),
    '000080.KS': ('하이트진로', 'Hite Jinro', ['하이트진로']),
    '005830.KS': ('DB손해보험', 'DB Insurance', ['db손해보험', 'DB손해보험']),
    '006360.KS': ('GS건설', 'GS E&C', ['gs건설', 'GS건설']),
    '078930.KS': ('GS', 'GS', ['gs', 'GS']),
    '007070.KS': ('GS리테일', 'GS Retail', ['gs리테일', 'GS리테일']),
    
    # 중견/중소형주 (시총 100~500위)
    '326030.KS': ('SK바이오팜', 'SK Biopharm', ['sk바이오팜', 'SK바이오팜']),
    '068760.KS': ('셀트리온제약', 'Celltrion Pharm', ['셀트리온제약']),
    '091990.KS': ('셀트리온헬스케어', 'Celltrion Healthcare', ['셀트리온헬스케어']),
    '128940.KS': ('한미약품', 'Hanmi Pharm', ['한미약품']),
    '196170.KS': ('알테오젠', 'Alteogen', ['알테오젠']),
    '214450.KS': ('파마리서치', 'Pharma Research', ['파마리서치']),
    '302440.KS': ('SK바이오사이언스', 'SK Bioscience', ['sk바이오사이언스', 'SK바이오사이언스']),
    '336260.KS': ('두산퓨얼셀', 'Doosan Fuel Cell', ['두산퓨얼셀']),
    '267250.KS': ('HD현대', 'HD Hyundai', ['hd현대', 'HD현대']),
    '267260.KS': ('HD현대중공업', 'HD Hyundai Heavy', ['hd현대중공업', 'HD현대중공업']),
    '329180.KS': ('HD현대마린엔진', 'HD Hyundai Marine Engine', ['hd현대마린엔진']),
    '042670.KS': ('HD현대인프라코어', 'HD Hyundai Infracore', ['hd현대인프라코어']),
    '010620.KS': ('현대미포조선', 'Hyundai Mipo', ['현대미포조선']),
    '011210.KS': ('현대위아', 'Hyundai Wia', ['현대위아']),
    '005385.KS': ('현대차우', 'Hyundai Motor Pref', ['현대차우']),
    '012630.KS': ('HDC', 'HDC', ['hdc', 'HDC']),
    '069960.KS': ('현대백화점', 'Hyundai Dept Store', ['현대백화점']),
    '004370.KS': ('농심', 'Nongshim', ['농심']),
    '005300.KS': ('롯데칠성', 'Lotte Chilsung', ['롯데칠성']),
    '001680.KS': ('대상', 'Daesang', ['대상']),
    '003920.KS': ('남양유업', 'Namyang Dairy', ['남양유업']),
    '280360.KS': ('롯데웰푸드', 'Lotte Wellfood', ['롯데웰푸드']),
}

# 미국 주요 기업 (S&P 500 + 나스닥 100)
USA_TOP_COMPANIES = {
    # 빅테크
    'AAPL': ('애플', 'Apple', ['애플', 'apple', '아이폰']),
    'MSFT': ('마이크로소프트', 'Microsoft', ['마이크로소프트', 'microsoft', 'ms']),
    'GOOGL': ('구글', 'Alphabet (Google)', ['구글', 'google', 'alphabet']),
    'GOOG': ('구글', 'Alphabet Class C', ['구글', 'google']),
    'AMZN': ('아마존', 'Amazon', ['아마존', 'amazon']),
    'NVDA': ('엔비디아', 'NVIDIA', ['엔비디아', 'nvidia']),
    'META': ('메타', 'Meta (Facebook)', ['메타', 'meta', '페이스북', 'facebook']),
    'TSLA': ('테슬라', 'Tesla', ['테슬라', 'tesla']),
    
    # 반도체/하드웨어
    'INTC': ('인텔', 'Intel', ['인텔', 'intel']),
    'AMD': ('AMD', 'AMD', ['amd', 'AMD']),
    'QCOM': ('퀄컴', 'Qualcomm', ['퀄컴', 'qualcomm']),
    'TXN': ('텍사스인스트루먼트', 'Texas Instruments', ['ti', 'texas instruments']),
    'AVGO': ('브로드컴', 'Broadcom', ['브로드컴', 'broadcom']),
    'MU': ('마이크론', 'Micron', ['마이크론', 'micron']),
    'ADI': ('아날로그디바이스', 'Analog Devices', ['analog devices', 'adi']),
    'LRCX': ('램리서치', 'Lam Research', ['lam research']),
    'AMAT': ('어플라이드머티리얼즈', 'Applied Materials', ['applied materials']),
    'KLAC': ('KLA', 'KLA', ['kla']),
    
    # 소프트웨어/클라우드
    'CRM': ('세일즈포스', 'Salesforce', ['세일즈포스', 'salesforce']),
    'ORCL': ('오라클', 'Oracle', ['오라클', 'oracle']),
    'ADBE': ('어도비', 'Adobe', ['어도비', 'adobe']),
    'NOW': ('서비스나우', 'ServiceNow', ['servicenow']),
    'INTU': ('인튜잇', 'Intuit', ['intuit']),
    'PANW': ('팔로알토네트웍스', 'Palo Alto Networks', ['palo alto']),
    'SNPS': ('시놉시스', 'Synopsys', ['synopsys']),
    'CDNS': ('케이던스', 'Cadence', ['cadence']),
    
    # 전기차/자동차
    'F': ('포드', 'Ford', ['포드', 'ford']),
    'GM': ('GM', 'General Motors', ['gm', '제너럴모터스']),
    'RIVN': ('리비안', 'Rivian', ['리비안', 'rivian']),
    'LCID': ('루시드', 'Lucid', ['루시드', 'lucid']),
    'NIO': ('니오', 'NIO', ['니오', 'nio']),
    'XPEV': ('샤오펑', 'XPeng', ['샤오펑', 'xpeng']),
    
    # 항공우주/방산
    'BA': ('보잉', 'Boeing', ['보잉', 'boeing']),
    'LMT': ('록히드마틴', 'Lockheed Martin', ['록히드마틴', 'lockheed']),
    'RTX': ('레이시온', 'Raytheon', ['레이시온', 'raytheon']),
    'NOC': ('노스럽그루먼', 'Northrop Grumman', ['northrop grumman']),
    'GD': ('제너럴다이내믹스', 'General Dynamics', ['general dynamics']),
    
    # 소비재
    'NKE': ('나이키', 'Nike', ['나이키', 'nike']),
    'SBUX': ('스타벅스', 'Starbucks', ['스타벅스', 'starbucks']),
    'MCD': ('맥도날드', 'McDonald\'s', ['맥도날드', 'mcdonalds']),
    'KO': ('코카콜라', 'Coca-Cola', ['코카콜라', 'coca cola', 'coke']),
    'PEP': ('펩시', 'PepsiCo', ['펩시', 'pepsi']),
    'PG': ('P&G', 'Procter & Gamble', ['p&g', 'pg']),
    'COST': ('코스트코', 'Costco', ['코스트코', 'costco']),
    'WMT': ('월마트', 'Walmart', ['월마트', 'walmart']),
    'TGT': ('타겟', 'Target', ['타겟', 'target']),
    'HD': ('홈디포', 'Home Depot', ['홈디포', 'homedepot']),
    'LOW': ('로우스', 'Lowe\'s', ['lowes']),
    
    # 금융
    'JPM': ('JP모건', 'JPMorgan Chase', ['jp모건', 'jpmorgan']),
    'BAC': ('뱅크오브아메리카', 'Bank of America', ['뱅크오브아메리카', 'bank of america', 'boa']),
    'WFC': ('웰스파고', 'Wells Fargo', ['웰스파고', 'wells fargo']),
    'GS': ('골드만삭스', 'Goldman Sachs', ['골드만삭스', 'goldman sachs']),
    'MS': ('모건스탠리', 'Morgan Stanley', ['모건스탠리', 'morgan stanley']),
    'C': ('씨티그룹', 'Citigroup', ['씨티그룹', 'citigroup', 'citi']),
    'BLK': ('블랙록', 'BlackRock', ['블랙록', 'blackrock']),
    'SCHW': ('찰스슈왑', 'Charles Schwab', ['charles schwab']),
    'V': ('비자', 'Visa', ['비자', 'visa']),
    'MA': ('마스터카드', 'Mastercard', ['마스터카드', 'mastercard']),
    'AXP': ('아메리칸익스프레스', 'American Express', ['american express', 'amex']),
    'PYPL': ('페이팔', 'PayPal', ['페이팔', 'paypal']),
    
    # 헬스케어/제약
    'JNJ': ('존슨앤존슨', 'Johnson & Johnson', ['존슨앤존슨', 'johnson', 'jnj']),
    'UNH': ('유나이티드헬스', 'UnitedHealth', ['unitedhealth']),
    'PFE': ('화이자', 'Pfizer', ['화이자', 'pfizer']),
    'ABBV': ('애브비', 'AbbVie', ['abbvie']),
    'TMO': ('써모피셔', 'Thermo Fisher', ['thermo fisher']),
    'MRK': ('머크', 'Merck', ['머크', 'merck']),
    'LLY': ('일라이릴리', 'Eli Lilly', ['eli lilly']),
    'ABT': ('애보트', 'Abbott', ['abbott']),
    'DHR': ('다나허', 'Danaher', ['danaher']),
    'BMY': ('브리스톨마이어스', 'Bristol Myers', ['bristol myers']),
    'AMGN': ('암젠', 'Amgen', ['amgen']),
    'GILD': ('길리어드', 'Gilead', ['gilead']),
    'VRTX': ('버텍스', 'Vertex', ['vertex']),
    'REGN': ('리제네론', 'Regeneron', ['regeneron']),
    'BIIB': ('바이오젠', 'Biogen', ['biogen']),
    'MRNA': ('모더나', 'Moderna', ['모더나', 'moderna']),
    
    # 에너지
    'XOM': ('엑손모빌', 'Exxon Mobil', ['엑손모빌', 'exxon']),
    'CVX': ('셰브론', 'Chevron', ['셰브론', 'chevron']),
    'COP': ('코노코필립스', 'ConocoPhillips', ['conocophillips']),
    'SLB': ('슐럼버거', 'Schlumberger', ['schlumberger']),
    'EOG': ('EOG에너지', 'EOG Resources', ['eog']),
    
    # 통신
    'T': ('AT&T', 'AT&T', ['att', 'at&t']),
    'VZ': ('버라이즌', 'Verizon', ['버라이즌', 'verizon']),
    'TMUS': ('T모바일', 'T-Mobile', ['t모바일', 't-mobile']),
    'CMCSA': ('컴캐스트', 'Comcast', ['comcast']),
    
    # 미디어/엔터테인먼트
    'DIS': ('디즈니', 'Disney', ['디즈니', 'disney']),
    'NFLX': ('넷플릭스', 'Netflix', ['넷플릭스', 'netflix']),
    'WBD': ('워너브라더스', 'Warner Bros Discovery', ['warner bros']),
    'PARA': ('파라마운트', 'Paramount', ['paramount']),
    
    # 유틸리티/산업재
    'CAT': ('캐터필러', 'Caterpillar', ['caterpillar', 'cat']),
    'DE': ('디어', 'Deere', ['deere', 'john deere']),
    'MMM': ('3M', '3M', ['3m']),
    'HON': ('허니웰', 'Honeywell', ['honeywell']),
    'UPS': ('UPS', 'UPS', ['ups']),
    'FDX': ('페덱스', 'FedEx', ['페덱스', 'fedex']),
    'GE': ('GE', 'General Electric', ['ge', 'general electric']),
    
    # 부동산
    'AMT': ('아메리칸타워', 'American Tower', ['american tower']),
    'PLD': ('프롤로지스', 'Prologis', ['prologis']),
    'CCI': ('크라운캐슬', 'Crown Castle', ['crown castle']),
    'EQIX': ('에퀴닉스', 'Equinix', ['equinix']),
    
    # 소매/전자상거래
    'BABA': ('알리바바', 'Alibaba', ['알리바바', 'alibaba']),
    'JD': ('JD닷컴', 'JD.com', ['jd', 'jd닷컴']),
    'PDD': ('핀둬둬', 'PDD (Pinduoduo)', ['pdd', '핀둬둬']),
    'SHOP': ('쇼피파이', 'Shopify', ['shopify']),
    'EBAY': ('이베이', 'eBay', ['이베이', 'ebay']),
    
    # 기타 테크
    'UBER': ('우버', 'Uber', ['우버', 'uber']),
    'LYFT': ('리프트', 'Lyft', ['리프트', 'lyft']),
    'ABNB': ('에어비앤비', 'Airbnb', ['에어비앤비', 'airbnb']),
    'DASH': ('도어대시', 'DoorDash', ['도어대시', 'doordash']),
    'SPOT': ('스포티파이', 'Spotify', ['스포티파이', 'spotify']),
    'SNAP': ('스냅', 'Snap', ['스냅', 'snap', '스냅챗']),
    'PINS': ('핀터레스트', 'Pinterest', ['핀터레스트', 'pinterest']),
    'TWTR': ('트위터', 'Twitter', ['트위터', 'twitter']),
    'RBLX': ('로블록스', 'Roblox', ['로블록스', 'roblox']),
    'U': ('유니티', 'Unity', ['유니티', 'unity']),
    'ZM': ('줌', 'Zoom', ['줌', 'zoom']),
    'DOCU': ('도큐사인', 'DocuSign', ['docusign']),
    'DDOG': ('데이터독', 'Datadog', ['datadog']),
    'SNOW': ('스노우플레이크', 'Snowflake', ['snowflake']),
    'PLTR': ('팔란티어', 'Palantir', ['팔란티어', 'palantir']),
    'SQ': ('블록', 'Block (Square)', ['블록', 'block', 'square']),
    'COIN': ('코인베이스', 'Coinbase', ['코인베이스', 'coinbase']),
}

def build_search_map():
    """검색어 → 티커 매핑 테이블 생성"""
    search_map = {}
    
    # 한국 기업
    for ticker, (kr_name, en_name, keywords) in KOREA_TOP_COMPANIES.items():
        for keyword in keywords:
            search_map[keyword.lower()] = ticker
        # 한글명, 영문명도 추가
        search_map[kr_name.lower()] = ticker
        search_map[en_name.lower()] = ticker
    
    # 미국 기업
    for ticker, (kr_name, en_name, keywords) in USA_TOP_COMPANIES.items():
        for keyword in keywords:
            search_map[keyword.lower()] = ticker
        # 한글명, 영문명도 추가
        search_map[kr_name.lower()] = ticker
        search_map[en_name.lower()] = ticker
        # 티커 자체도 추가
        search_map[ticker.lower()] = ticker
    
    return search_map

# 글로벌 검색 맵
SEARCH_MAP = build_search_map()

def search_ticker_advanced(query: str) -> str:
    """
    고급 검색 - 한글/영문/약어 모두 지원
    
    Args:
        query: 검색어 (예: "두산에너빌리티", "doosan", "AAPL", "애플")
    
    Returns:
        티커 코드 (예: "034020.KS", "AAPL")
    """
    if not query:
        return query
    
    # 공백 제거 및 소문자 변환
    clean_query = query.strip().lower().replace(' ', '')
    
    # 직접 매핑 확인
    if clean_query in SEARCH_MAP:
        return SEARCH_MAP[clean_query]
    
    # 부분 매칭 (예: "삼성" → "삼성전자")
    matches = []
    for keyword, ticker in SEARCH_MAP.items():
        if clean_query in keyword or keyword in clean_query:
            matches.append((ticker, keyword))
    
    if matches:
        # 가장 짧은 매칭 (더 정확한 결과)
        matches.sort(key=lambda x: len(x[1]))
        return matches[0][0]
    
    # 매칭 실패 시 원본 반환 (티커 코드일 수 있음)
    return query.upper()
