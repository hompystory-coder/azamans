<?php
/**
 * CKEditor 5 Editor Loader
 * 
 * 사용법:
 * 1. 기본 사용:
 *    include '/home/mvc/editor.php';
 *    initCKEditor('content'); // textarea ID
 * 
 * 2. 커스텀 설정:
 *    include '/home/mvc/editor.php';
 *    initCKEditor('content', [
 *        'height' => 500,
 *        'imageUploadUrl' => '/upload/image',
 *        'toolbar' => [...] // 커스텀 툴바
 *    ]);
 */

/**
 * CKEditor 초기화 함수
 * 
 * @param string $textareaId textarea의 ID
 * @param array $options 에디터 옵션 (선택)
 * @return void
 */
function initCKEditor($textareaId = 'editor', $options = []) {
    // 기본 설정
    $editorDefaults = [
        'height' => 500,
        'minHeight' => 300,
        'imageUploadUrl' => '/bbs/uploadImage',
        'language' => 'ko',
        'toolbar' => [
            'findAndReplace', '|',
            'heading', '|',
            'fontSize', 'fontFamily', 'fontColor', 'fontBackgroundColor', '|',
            'bold', 'italic', 'underline', 'strikethrough', '|',
            'link', 'uploadImage', 'insertTable', 'blockQuote', 'mediaEmbed', '|',
            'alignment', '|',
            'bulletedList', 'numberedList', 'todoList', '|',
            'outdent', 'indent', '|',
            'code', 'codeBlock', '|',
            'highlight', 'removeFormat', '|',
            'specialCharacters', 'horizontalLine', 'pageBreak', '|',
            'htmlEmbed', 'sourceEditing', '|',
            'undo', 'redo'
        ]
    ];
    
    // 옵션 병합
    $config = array_merge($editorDefaults, $options);
    
    // JSON 변환
    $toolbarJson = json_encode($config['toolbar']);
    $imageUploadUrl = $config['imageUploadUrl'];
    $language = $config['language'];
    $height = $config['height'];
    $minHeight = $config['minHeight'];
    
    ?>
    <style>
    .ck-editor__editable {
        min-height: <?php echo $minHeight; ?>px !important;
        height: <?php echo $height; ?>px !important;
    }
    .ck-powered-by {
        display: none !important;
    }
    </style>
    
    <script src="/public/plugins/editor/build/ckeditor.js"></script>
    <script>
    (function() {
        // 커스텀 업로드 어댑터
        class CustomUploadAdapter {
            constructor(loader) {
                this.loader = loader;
            }

            upload() {
                return this.loader.file
                    .then(file => new Promise((resolve, reject) => {
                        this._initRequest();
                        this._initListeners(resolve, reject, file);
                        this._sendRequest(file);
                    }));
            }

            abort() {
                if (this.xhr) {
                    this.xhr.abort();
                }
            }

            _initRequest() {
                const xhr = this.xhr = new XMLHttpRequest();
                xhr.open('POST', '<?php echo $imageUploadUrl; ?>', true);
                xhr.responseType = 'json';
            }

            _initListeners(resolve, reject, file) {
                const xhr = this.xhr;
                const loader = this.loader;
                const genericErrorText = `파일을 업로드할 수 없습니다: ${file.name}.`;

                xhr.addEventListener('error', () => reject(genericErrorText));
                xhr.addEventListener('abort', () => reject());
                xhr.addEventListener('load', () => {
                    const response = xhr.response;

                    if (!response || response.error) {
                        return reject(response && response.error ? response.error.message : genericErrorText);
                    }

                    resolve({
                        default: response.url
                    });
                });

                if (xhr.upload) {
                    xhr.upload.addEventListener('progress', evt => {
                        if (evt.lengthComputable) {
                            loader.uploadTotal = evt.total;
                            loader.uploaded = evt.loaded;
                        }
                    });
                }
            }

            _sendRequest(file) {
                const data = new FormData();
                data.append('upload', file);
                this.xhr.send(data);
            }
        }

        function CustomUploadAdapterPlugin(editor) {
            editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
                return new CustomUploadAdapter(loader);
            };
        }

        // CKEditor 초기화
        ClassicEditor
            .create(document.querySelector('#<?php echo $textareaId; ?>'), {
                toolbar: {
                    items: <?php echo $toolbarJson; ?>,
                    shouldNotGroupWhenFull: true
                },
                language: '<?php echo $language; ?>',
                image: {
                    toolbar: [
                        'imageTextAlternative',
                        'toggleImageCaption',
                        '|',
                        'imageStyle:inline',
                        'imageStyle:wrapText',
                        'imageStyle:breakText',
                        '|',
                        'resizeImage',
                        '|',
                        'linkImage'
                    ],
                    styles: [
                        'inline',
                        'alignLeft',
                        'alignCenter',
                        'alignRight',
                        'alignBlockLeft',
                        'alignBlockRight',
                        'block',
                        'side'
                    ]
                },
                table: {
                    contentToolbar: [
                        'tableColumn',
                        'tableRow',
                        'mergeTableCells',
                        'tableCellProperties',
                        'tableProperties',
                        'toggleTableCaption'
                    ]
                },
                mediaEmbed: {
                    toolbar: []
                },
                fontFamily: {
                    options: [
                        '나눔고딕, NanumGothic',
                        '맑은고딕, Malgun Gothic',
                        '돋움, Dotum',
                        '굴림, Gulim',
                        'default',
                        'Arial, Helvetica, sans-serif',
                        'Courier New, Courier, monospace',
                        'Georgia, serif',
                        'Times New Roman, Times, serif',
                        'Verdana, Geneva, sans-serif'
                    ],
                    supportAllValues: true
                },
                fontSize: {
                    options: [9, 10, 11, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 36, 40, 48],
                    supportAllValues: true
                },
                heading: {
                    options: [
                        { model: 'paragraph', title: '본문', class: 'ck-heading_paragraph' },
                        { model: 'heading1', view: 'h1', title: '제목 1', class: 'ck-heading_heading1' },
                        { model: 'heading2', view: 'h2', title: '제목 2', class: 'ck-heading_heading2' },
                        { model: 'heading3', view: 'h3', title: '제목 3', class: 'ck-heading_heading3' },
                        { model: 'heading4', view: 'h4', title: '제목 4', class: 'ck-heading_heading4' },
                        { model: 'heading5', view: 'h5', title: '제목 5', class: 'ck-heading_heading5' },
                        { model: 'heading6', view: 'h6', title: '제목 6', class: 'ck-heading_heading6' }
                    ]
                },
                htmlSupport: {
                    allow: [
                        {
                            name: /.*/,
                            attributes: true,
                            classes: true,
                            styles: true
                        }
                    ]
                },
                htmlEmbed: {
                    showPreviews: true
                },
                link: {
                    addTargetToExternalLinks: true,
                    defaultProtocol: 'https://',
                    decorators: {
                        toggleDownloadable: {
                            mode: 'manual',
                            label: '다운로드 가능',
                            attributes: {
                                download: 'file'
                            }
                        },
                        openInNewTab: {
                            mode: 'manual',
                            label: '새 탭에서 열기',
                            defaultValue: true,
                            attributes: {
                                target: '_blank',
                                rel: 'noopener noreferrer'
                            }
                        }
                    }
                },
                list: {
                    properties: {
                        styles: true,
                        startIndex: true,
                        reversed: true
                    }
                },
                placeholder: '내용을 입력하세요...',
                extraPlugins: [CustomUploadAdapterPlugin],
                licenseKey: 'GPL'
            })
            .then(editor => {
                window.editor<?php echo $textareaId; ?> = editor;
                console.log('✅ CKEditor 5 초기화 완료:', '<?php echo $textareaId; ?>');
                
                // WordCount 플러그인 추가
                const wordCountPlugin = editor.plugins.get('WordCount');
                const wordCountContainer = document.getElementById('word-count-<?php echo $textareaId; ?>');
                if (wordCountContainer) {
                    wordCountContainer.appendChild(wordCountPlugin.wordCountContainer);
                }
            })
            .catch(error => {
                console.error('❌ CKEditor 초기화 오류:', error);
            });
    })();
    </script>
    
    <!-- WordCount 표시 영역 (선택) -->
    <div id="word-count-<?php echo $textareaId; ?>" style="margin-top: 10px; text-align: right; color: #666;"></div>
    <?php
}
?>
