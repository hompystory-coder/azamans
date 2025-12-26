#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
특허 출원용 시스템 도면 생성 스크립트
"""

import matplotlib.pyplot as plt
import matplotlib.patches as patches
from matplotlib.patches import FancyBboxPatch, FancyArrowPatch
import numpy as np

# 한글 폰트 설정
plt.rcParams['font.family'] = 'DejaVu Sans'
plt.rcParams['axes.unicode_minus'] = False

def create_figure(title, figsize=(12, 8)):
    """기본 figure 생성"""
    fig, ax = plt.subplots(figsize=figsize)
    ax.set_xlim(0, 10)
    ax.set_ylim(0, 10)
    ax.axis('off')
    ax.set_title(title, fontsize=16, fontweight='bold', pad=20)
    return fig, ax

def add_box(ax, x, y, width, height, text, color='lightblue', textsize=10):
    """텍스트가 있는 박스 추가"""
    box = FancyBboxPatch(
        (x, y), width, height,
        boxstyle="round,pad=0.1",
        edgecolor='black',
        facecolor=color,
        linewidth=2
    )
    ax.add_patch(box)
    
    # 텍스트를 여러 줄로 분리
    lines = text.split('\n')
    for i, line in enumerate(lines):
        ax.text(
            x + width/2, 
            y + height/2 - (i-len(lines)/2+0.5)*0.15,
            line,
            ha='center', va='center',
            fontsize=textsize, fontweight='bold'
        )

def add_arrow(ax, x1, y1, x2, y2, label=''):
    """화살표 추가"""
    arrow = FancyArrowPatch(
        (x1, y1), (x2, y2),
        arrowstyle='->,head_width=0.4,head_length=0.8',
        color='black',
        linewidth=2
    )
    ax.add_patch(arrow)
    
    if label:
        mid_x, mid_y = (x1 + x2) / 2, (y1 + y2) / 2
        ax.text(mid_x, mid_y + 0.2, label, ha='center', fontsize=9,
                bbox=dict(boxstyle='round,pad=0.3', facecolor='white', edgecolor='gray'))

# 도면 1: 전체 시스템 아키텍처
def create_diagram1():
    """도면 1: 전체 시스템 구조"""
    fig, ax = create_figure('[Drawing 1] Overall System Architecture', figsize=(14, 10))
    
    # 사용자 레이어
    add_box(ax, 0.5, 8.5, 2, 1, 'User Input\n"cat"', 'lightgreen')
    
    # AI 엔진 레이어
    add_box(ax, 0.5, 6.5, 2, 1.5, 'AI Prompt\nEnhancer', 'lightblue')
    add_box(ax, 3, 6.5, 2, 1.5, 'Smart Style\nSelector', 'lightblue')
    add_box(ax, 5.5, 6.5, 2, 1.5, 'WebGPU\nEngine', 'lightblue')
    
    # 생성 파이프라인
    add_box(ax, 0.5, 4.5, 1.8, 1.2, 'Image Gen\n(3 scenes)', 'lightyellow')
    add_box(ax, 2.8, 4.5, 1.8, 1.2, 'TTS\nVoice', 'lightyellow')
    add_box(ax, 5.1, 4.5, 1.8, 1.2, 'Video\nRender', 'lightyellow')
    add_box(ax, 7.4, 4.5, 1.8, 1.2, 'Final\nCompose', 'lightyellow')
    
    # 출력
    add_box(ax, 3.5, 2.5, 3, 1.2, 'AI-Generated\nShort Video', 'lightcoral')
    
    # 플랫폼 출력
    add_box(ax, 1, 0.5, 1.5, 1, 'YouTube\nShorts', 'lavender', 8)
    add_box(ax, 3, 0.5, 1.5, 1, 'TikTok', 'lavender', 8)
    add_box(ax, 5, 0.5, 1.5, 1, 'Instagram\nReels', 'lavender', 8)
    add_box(ax, 7, 0.5, 1.5, 1, 'Square\n1:1', 'lavender', 8)
    
    # 화살표
    add_arrow(ax, 1.5, 8.5, 1.5, 8.0, '')
    add_arrow(ax, 1.5, 6.5, 1.4, 5.7, '')
    add_arrow(ax, 4, 6.5, 3.7, 5.7, '')
    add_arrow(ax, 6.5, 6.5, 2.8, 5.5, '')
    
    add_arrow(ax, 2.3, 4.5, 2.8, 4.5, '')
    add_arrow(ax, 4.6, 4.5, 5.1, 4.5, '')
    add_arrow(ax, 6.9, 4.5, 7.4, 4.5, '')
    
    add_arrow(ax, 5, 3.7, 5, 3.7, '')
    add_arrow(ax, 5, 2.5, 2.5, 1.5, '')
    add_arrow(ax, 5, 2.5, 3.75, 1.5, '')
    add_arrow(ax, 5, 2.5, 5.75, 1.5, '')
    add_arrow(ax, 5, 2.5, 7.75, 1.5, '')
    
    plt.tight_layout()
    plt.savefig('public/downloads/patent_diagram_1.png', dpi=300, bbox_inches='tight')
    print("✅ 도면 1 생성 완료: patent_diagram_1.png")
    plt.close()

# 도면 2: AI 프롬프트 확장 상세
def create_diagram2():
    """도면 2: AI Prompt Enhancer 상세"""
    fig, ax = create_figure('[Drawing 2] AI Prompt Enhancer Detail', figsize=(12, 10))
    
    # 입력
    add_box(ax, 3.5, 8.5, 3, 0.8, 'Simple Input: "cat"', 'lightgreen')
    
    # 분석 모듈들
    add_box(ax, 0.5, 6.5, 2, 1.2, 'Keyword\nAnalysis', 'lightyellow')
    add_box(ax, 3, 6.5, 2, 1.2, 'Mood\nDetection', 'lightyellow')
    add_box(ax, 5.5, 6.5, 2, 1.2, 'Context\nMapping', 'lightyellow')
    add_box(ax, 8, 6.5, 1.5, 1.2, 'Visual\nDetails', 'lightyellow')
    
    # 확장 레이어
    add_box(ax, 0.5, 4.5, 2, 1, 'Lighting\n7 types', 'lightblue', 8)
    add_box(ax, 2.8, 4.5, 2, 1, 'Colors\n6 schemes', 'lightblue', 8)
    add_box(ax, 5.1, 4.5, 2, 1, 'Composition\n6 styles', 'lightblue', 8)
    add_box(ax, 7.4, 4.5, 2, 1, 'Quality\nterms', 'lightblue', 8)
    
    # 장면 생성
    add_box(ax, 1, 2.5, 2.2, 1, 'Scene 1\nIntro', 'lavender')
    add_box(ax, 3.9, 2.5, 2.2, 1, 'Scene 2\nAction', 'lavender')
    add_box(ax, 6.8, 2.5, 2.2, 1, 'Scene 3\nCloseup', 'lavender')
    
    # 최종 출력
    add_box(ax, 2, 0.5, 6, 1.2, 'Enhanced Prompt (5-10x longer)', 'lightcoral')
    
    # 화살표들
    add_arrow(ax, 5, 8.5, 1.5, 7.7, '')
    add_arrow(ax, 5, 8.5, 4, 7.7, '')
    add_arrow(ax, 5, 8.5, 6.5, 7.7, '')
    add_arrow(ax, 5, 8.5, 8.75, 7.7, '')
    
    add_arrow(ax, 1.5, 6.5, 1.5, 5.5, '')
    add_arrow(ax, 4, 6.5, 3.8, 5.5, '')
    add_arrow(ax, 6.5, 6.5, 6.1, 5.5, '')
    add_arrow(ax, 8.75, 6.5, 8.4, 5.5, '')
    
    add_arrow(ax, 1.5, 4.5, 2.1, 3.5, '')
    add_arrow(ax, 3.8, 4.5, 5, 3.5, '')
    add_arrow(ax, 6.1, 4.5, 7.9, 3.5, '')
    
    add_arrow(ax, 2.1, 2.5, 3, 1.7, '')
    add_arrow(ax, 5, 2.5, 5, 1.7, '')
    add_arrow(ax, 7.9, 2.5, 7, 1.7, '')
    
    plt.tight_layout()
    plt.savefig('public/downloads/patent_diagram_2.png', dpi=300, bbox_inches='tight')
    print("✅ 도면 2 생성 완료: patent_diagram_2.png")
    plt.close()

# 도면 3: 스마트 스타일 선택기
def create_diagram3():
    """도면 3: Smart Style Selector"""
    fig, ax = create_figure('[Drawing 3] Smart Style Selector', figsize=(14, 10))
    
    # 입력
    add_box(ax, 3, 8.5, 4, 0.8, 'Enhanced Prompt + Context', 'lightgreen')
    
    # 분석
    add_box(ax, 1, 6.8, 2.5, 1.2, 'Keyword\nMatching\n(+3 points)', 'lightyellow', 9)
    add_box(ax, 3.8, 6.8, 2.5, 1.2, 'Mood\nMatching\n(+5 points)', 'lightyellow', 9)
    add_box(ax, 6.6, 6.8, 2.5, 1.2, 'Type\nMatching\n(+4 points)', 'lightyellow', 9)
    
    # 스타일 프리셋들
    styles = [
        ('Anime', 'lightblue'),
        ('Cyberpunk', 'lightblue'),
        ('Fantasy', 'lightblue'),
        ('Minimal', 'lightblue'),
        ('Vintage', 'lightblue'),
        ('Nature', 'lightblue'),
        ('Horror', 'lightcyan'),
        ('Comedy', 'lightcyan'),
        ('Education', 'lightcyan'),
        ('Motivation', 'lightcyan')
    ]
    
    for i, (style, color) in enumerate(styles):
        x = 0.5 + (i % 5) * 1.9
        y = 4.5 - (i // 5) * 1.3
        add_box(ax, x, y, 1.6, 0.8, style, color, 8)
    
    # 선택된 스타일
    add_box(ax, 3.5, 1.5, 3, 1, 'Selected Style\n+ Alternatives', 'lightcoral')
    
    # 출력 설정
    add_box(ax, 1, 0.2, 1.8, 0.8, 'Platform:\nYouTube', 'lavender', 8)
    add_box(ax, 3.2, 0.2, 1.8, 0.8, 'Music:\nUpbeat', 'lavender', 8)
    add_box(ax, 5.4, 0.2, 1.8, 0.8, 'Voice:\nCheerful', 'lavender', 8)
    add_box(ax, 7.6, 0.2, 1.8, 0.8, 'Duration:\n30s', 'lavender', 8)
    
    # 화살표
    add_arrow(ax, 5, 8.5, 2.25, 8.0, '')
    add_arrow(ax, 5, 8.5, 5.05, 8.0, '')
    add_arrow(ax, 5, 8.5, 7.85, 8.0, '')
    
    add_arrow(ax, 5, 6.8, 5, 5.5, 'Scoring')
    add_arrow(ax, 5, 2.5, 5, 2.5, '')
    
    add_arrow(ax, 5, 1.5, 1.9, 1.0, '')
    add_arrow(ax, 5, 1.5, 4.1, 1.0, '')
    add_arrow(ax, 5, 1.5, 6.3, 1.0, '')
    add_arrow(ax, 5, 1.5, 8.5, 1.0, '')
    
    plt.tight_layout()
    plt.savefig('public/downloads/patent_diagram_3.png', dpi=300, bbox_inches='tight')
    print("✅ 도면 3 생성 완료: patent_diagram_3.png")
    plt.close()

# 도면 4: WebGPU 아키텍처
def create_diagram4():
    """도면 4: WebGPU Zero-Install Architecture"""
    fig, ax = create_figure('[Drawing 4] WebGPU Zero-Install Architecture', figsize=(12, 10))
    
    # 브라우저 레이어
    add_box(ax, 0.5, 8.5, 9, 0.8, 'Web Browser (Chrome, Edge, Firefox)', 'lightgreen')
    
    # WebGPU API
    add_box(ax, 1, 7, 8, 1, 'WebGPU API Layer', 'lightyellow')
    
    # AI 모델들
    add_box(ax, 0.5, 5, 2, 1.2, 'Stable\nDiffusion\nWASM', 'lightblue', 9)
    add_box(ax, 2.8, 5, 2, 1.2, 'ONNX\nRuntime\nWeb', 'lightblue', 9)
    add_box(ax, 5.1, 5, 2, 1.2, 'TTS\nEngine', 'lightblue', 9)
    add_box(ax, 7.4, 5, 2, 1.2, 'Video\nCodec', 'lightblue', 9)
    
    # GPU 레이어
    add_box(ax, 1, 3, 8, 1.2, 'Local GPU (NVIDIA, AMD, Intel)', 'lavender')
    
    # 하드웨어
    add_box(ax, 1.5, 1, 3, 1, 'User PC\nNo Installation\nRequired', 'lightcoral')
    add_box(ax, 5.5, 1, 3, 1, 'Private Data\nNever Leaves\nDevice', 'lightcoral')
    
    # 화살표
    add_arrow(ax, 5, 8.5, 5, 8.0, '')
    add_arrow(ax, 5, 7, 1.5, 6.2, '')
    add_arrow(ax, 5, 7, 3.8, 6.2, '')
    add_arrow(ax, 5, 7, 6.1, 6.2, '')
    add_arrow(ax, 5, 7, 8.4, 6.2, '')
    
    add_arrow(ax, 1.5, 5, 3, 4.2, '')
    add_arrow(ax, 3.8, 5, 4, 4.2, '')
    add_arrow(ax, 6.1, 5, 5.5, 4.2, '')
    add_arrow(ax, 8.4, 5, 6.5, 4.2, '')
    
    add_arrow(ax, 3, 3, 3, 2.0, '')
    add_arrow(ax, 7, 3, 7, 2.0, '')
    
    plt.tight_layout()
    plt.savefig('public/downloads/patent_diagram_4.png', dpi=300, bbox_inches='tight')
    print("✅ 도면 4 생성 완료: patent_diagram_4.png")
    plt.close()

# 도면 5: 원클릭 워크플로우
def create_diagram5():
    """도면 5: One-Click Generation Workflow"""
    fig, ax = create_figure('[Drawing 5] One-Click Generation Workflow', figsize=(14, 11))
    
    steps = [
        ('Step 1:\nUser Input', 'lightgreen', 8.5),
        ('Step 2:\nAI Analysis', 'lightyellow', 7.2),
        ('Step 3:\nPrompt Expand', 'lightyellow', 5.9),
        ('Step 4:\nStyle Select', 'lightyellow', 4.6),
        ('Step 5:\nImage Gen (3)', 'lightblue', 3.3),
        ('Step 6:\nTTS Voice', 'lightblue', 2.0),
        ('Step 7:\nVideo Compose', 'lightcoral', 0.7)
    ]
    
    for i, (text, color, y) in enumerate(steps):
        add_box(ax, 1.5, y, 2.5, 1, text, color, 10)
        
        # 시간 표시
        times = ['0s', '2s', '10s', '12s', '120s', '180s', '240s']
        ax.text(4.5, y + 0.5, f'Time: {times[i]}', fontsize=9, 
                bbox=dict(boxstyle='round', facecolor='white', edgecolor='gray'))
        
        # 진행률 바
        progress = (i + 1) / len(steps) * 100
        bar_length = 3 * (i + 1) / len(steps)
        bar = patches.Rectangle((6, y + 0.2), bar_length, 0.6, 
                                facecolor='green', alpha=0.6)
        ax.add_patch(bar)
        ax.text(7.5, y + 0.5, f'{int(progress)}%', fontsize=9, fontweight='bold')
        
        # 화살표 (마지막 제외)
        if i < len(steps) - 1:
            add_arrow(ax, 2.75, y, 2.75, steps[i+1][2] + 1.0, '')
    
    # 총 시간 표시
    add_box(ax, 3.5, -0.5, 3, 0.8, 'Total: ~4 minutes', 'lightgreen', 12)
    
    plt.tight_layout()
    plt.savefig('public/downloads/patent_diagram_5.png', dpi=300, bbox_inches='tight')
    print("✅ 도면 5 생성 완료: patent_diagram_5.png")
    plt.close()

# 도면 6: 데이터 흐름도
def create_diagram6():
    """도면 6: Data Flow Diagram"""
    fig, ax = create_figure('[Drawing 6] Data Flow & Processing Pipeline', figsize=(14, 10))
    
    # 입력 데이터
    add_box(ax, 1, 8.5, 2, 0.8, 'Text Input\n(Simple)', 'lightgreen')
    
    # 처리 단계들 (왼쪽)
    add_box(ax, 0.5, 7, 1.5, 0.8, 'Keywords', 'lightyellow', 8)
    add_box(ax, 0.5, 6, 1.5, 0.8, 'Mood', 'lightyellow', 8)
    add_box(ax, 0.5, 5, 1.5, 0.8, 'Context', 'lightyellow', 8)
    
    # 중앙 처리
    add_box(ax, 2.5, 5.5, 2, 2, 'AI Engine\nProcessing', 'lightblue')
    
    # 생성 단계들 (오른쪽)
    add_box(ax, 5, 7, 1.5, 0.8, 'Prompt', 'lavender', 8)
    add_box(ax, 5, 6, 1.5, 0.8, 'Style', 'lavender', 8)
    add_box(ax, 5, 5, 1.5, 0.8, 'Settings', 'lavender', 8)
    
    # GPU 처리
    add_box(ax, 7, 5.5, 2, 2, 'WebGPU\nExecution', 'lightyellow')
    
    # 생성 결과
    add_box(ax, 7.5, 3.5, 1.8, 0.7, 'Image 1', 'lightcoral', 8)
    add_box(ax, 7.5, 2.7, 1.8, 0.7, 'Image 2', 'lightcoral', 8)
    add_box(ax, 7.5, 1.9, 1.8, 0.7, 'Image 3', 'lightcoral', 8)
    add_box(ax, 7.5, 1.1, 1.8, 0.7, 'Audio', 'lightcoral', 8)
    
    # 최종 합성
    add_box(ax, 3.5, 0.2, 3, 0.8, 'Final Video Output', 'lightgreen')
    
    # 화살표들
    add_arrow(ax, 2, 8.5, 1.5, 7.8, '')
    add_arrow(ax, 2, 8.5, 1.5, 6.8, '')
    add_arrow(ax, 2, 8.5, 1.5, 5.8, '')
    
    add_arrow(ax, 2, 7, 2.5, 6.7, '')
    add_arrow(ax, 2, 6, 2.5, 6.3, '')
    add_arrow(ax, 2, 5, 2.5, 5.9, '')
    
    add_arrow(ax, 4.5, 6.5, 5, 7.4, '')
    add_arrow(ax, 4.5, 6.5, 5, 6.4, '')
    add_arrow(ax, 4.5, 6.5, 5, 5.4, '')
    
    add_arrow(ax, 6.5, 6.5, 7, 6.5, '')
    
    add_arrow(ax, 9, 6.5, 9.3, 3.9, '')
    add_arrow(ax, 9, 6.5, 9.3, 3.1, '')
    add_arrow(ax, 9, 6.5, 9.3, 2.3, '')
    add_arrow(ax, 9, 6.5, 9.3, 1.5, '')
    
    add_arrow(ax, 7.5, 2.5, 5.5, 1.0, '')
    
    plt.tight_layout()
    plt.savefig('public/downloads/patent_diagram_6.png', dpi=300, bbox_inches='tight')
    print("✅ 도면 6 생성 완료: patent_diagram_6.png")
    plt.close()

# 도면 7: 사용자 인터페이스
def create_diagram7():
    """도면 7: User Interface Flow"""
    fig, ax = create_figure('[Drawing 7] User Interface & Interaction Flow', figsize=(12, 10))
    
    # 메인 UI
    add_box(ax, 1, 8, 8, 1.2, 'Main Dashboard - Zero-Install AI Studio', 'lightgreen')
    
    # 모드 선택
    add_box(ax, 0.5, 6, 2.8, 1.2, 'One-Click\nMode\n(Beginner)', 'lightyellow')
    add_box(ax, 3.7, 6, 2.8, 1.2, 'Pro Mode\n(Advanced)', 'lightyellow')
    add_box(ax, 6.9, 6, 2.8, 1.2, 'Manual\nMode\n(Expert)', 'lightyellow')
    
    # 원클릭 플로우
    add_box(ax, 0.5, 4, 2.8, 1, 'Input:\n1 word', 'lightblue', 9)
    add_box(ax, 0.5, 2.5, 2.8, 1, 'AI Auto:\nAll steps', 'lightblue', 9)
    add_box(ax, 0.5, 1, 2.8, 1, 'Output:\nVideo ready', 'lightcoral', 9)
    
    # 프로 플로우
    add_box(ax, 3.7, 4, 2.8, 1, 'Style:\nSelect preset', 'lightblue', 9)
    add_box(ax, 3.7, 2.5, 2.8, 1, 'Adjust:\nFine-tune', 'lightblue', 9)
    add_box(ax, 3.7, 1, 2.8, 1, 'Preview:\n& Export', 'lightcoral', 9)
    
    # 수동 플로우
    add_box(ax, 6.9, 4, 2.8, 1, 'Full Control:\nAll params', 'lightblue', 9)
    add_box(ax, 6.9, 2.5, 2.8, 1, 'Custom:\nEvery detail', 'lightblue', 9)
    add_box(ax, 6.9, 1, 2.8, 1, 'Advanced:\nExport opts', 'lightcoral', 9)
    
    # 화살표
    add_arrow(ax, 3, 8, 1.9, 7.2, '')
    add_arrow(ax, 5, 8, 5.1, 7.2, '')
    add_arrow(ax, 7, 8, 8.3, 7.2, '')
    
    add_arrow(ax, 1.9, 6, 1.9, 5.0, '')
    add_arrow(ax, 1.9, 4, 1.9, 3.5, '')
    add_arrow(ax, 1.9, 2.5, 1.9, 2.0, '')
    
    add_arrow(ax, 5.1, 6, 5.1, 5.0, '')
    add_arrow(ax, 5.1, 4, 5.1, 3.5, '')
    add_arrow(ax, 5.1, 2.5, 5.1, 2.0, '')
    
    add_arrow(ax, 8.3, 6, 8.3, 5.0, '')
    add_arrow(ax, 8.3, 4, 8.3, 3.5, '')
    add_arrow(ax, 8.3, 2.5, 8.3, 2.0, '')
    
    # 시간 비교
    ax.text(1.9, 0.3, '~4 min', ha='center', fontsize=11, fontweight='bold', color='green')
    ax.text(5.1, 0.3, '~6 min', ha='center', fontsize=11, fontweight='bold', color='blue')
    ax.text(8.3, 0.3, '~10 min', ha='center', fontsize=11, fontweight='bold', color='red')
    
    plt.tight_layout()
    plt.savefig('public/downloads/patent_diagram_7.png', dpi=300, bbox_inches='tight')
    print("✅ 도면 7 생성 완료: patent_diagram_7.png")
    plt.close()

def main():
    """메인 실행 함수"""
    print("🎨 특허 출원용 시스템 도면 생성 시작...\n")
    
    create_diagram1()
    create_diagram2()
    create_diagram3()
    create_diagram4()
    create_diagram5()
    create_diagram6()
    create_diagram7()
    
    print("\n🎉 모든 도면 생성 완료!")
    print("\n📦 생성된 도면 목록:")
    print("  1. patent_diagram_1.png - 전체 시스템 아키텍처")
    print("  2. patent_diagram_2.png - AI 프롬프트 확장기 상세")
    print("  3. patent_diagram_3.png - 스마트 스타일 선택기")
    print("  4. patent_diagram_4.png - WebGPU 아키텍처")
    print("  5. patent_diagram_5.png - 원클릭 워크플로우")
    print("  6. patent_diagram_6.png - 데이터 흐름도")
    print("  7. patent_diagram_7.png - 사용자 인터페이스 흐름")
    print("\n📂 저장 위치: public/downloads/")

if __name__ == '__main__':
    main()
