{{-- Shared Quill editor CSS — same core styling used by thread-create.blade.php
     and thread.blade.php's reply editor. Include inside a @push('head') block. --}}
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,300;0,400;0,700;1,400&family=Prompt:ital,wght@0,300;0,400;0,600;1,400&family=Kanit:ital,wght@0,300;0,400;0,600;1,400&family=Noto+Serif+Thai:wght@400;700&family=Mitr:wght@300;400;600&family=Charm:wght@400;700&family=Trirong:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
<style>
    .ql-toolbar.ql-snow { background:#0b0b0b; border:1px solid #2a2a2a; border-radius:.5rem .5rem 0 0; }
    .ql-container.ql-snow { background:#090909; border:1px solid #2a2a2a; border-top:none; border-radius:0 0 .5rem .5rem; color:#e8e6e3; }
    .ql-toolbar.ql-snow button, .ql-toolbar.ql-snow .ql-picker-label { color:#e8e6e3; }
    .ql-toolbar.ql-snow button:hover, .ql-toolbar.ql-snow .ql-picker-label:hover,
    .ql-toolbar.ql-snow button.ql-active, .ql-toolbar.ql-snow .ql-picker-label.ql-active { color:#D4AF37 !important; }
    .ql-toolbar.ql-snow .ql-stroke { stroke:#e8e6e3; }
    .ql-toolbar.ql-snow button:hover .ql-stroke, .ql-toolbar.ql-snow button.ql-active .ql-stroke { stroke:#D4AF37; }
    .ql-toolbar.ql-snow .ql-fill { fill:#e8e6e3; }
    .ql-toolbar.ql-snow button:hover .ql-fill { fill:#D4AF37; }
    .ql-toolbar.ql-snow .ql-picker-options { background:#111; border:1px solid #2a2a2a; }
    .ql-toolbar.ql-snow .ql-picker-item { color:#e8e6e3; }
    .ql-toolbar.ql-snow .ql-picker-item:hover, .ql-toolbar.ql-snow .ql-picker-item.ql-selected { color:#D4AF37; }
    .ql-editor { min-height:260px; color:#e8e6e3; }
    .ql-editor.ql-blank::before { color:#686664; }
    .ql-editor a { color:#D4AF37; }
    .ql-editor p { margin-bottom:.75rem; }
    .ql-font-sarabun        { font-family:'Sarabun','Noto Sans Thai',sans-serif; }
    .ql-font-prompt         { font-family:'Prompt','Noto Sans Thai',sans-serif; }
    .ql-font-kanit          { font-family:'Kanit','Noto Sans Thai',sans-serif; }
    .ql-font-noto-serif-thai{ font-family:'Noto Serif Thai',Georgia,serif; }
    .ql-font-mitr           { font-family:'Mitr','Noto Sans Thai',sans-serif; }
    .ql-font-charm          { font-family:'Charm',cursive; }
    .ql-font-trirong        { font-family:'Trirong',Georgia,serif; }
    .ql-font-monospace      { font-family:SFMono-Regular,Consolas,monospace; }
    .ql-picker.ql-font .ql-picker-item[data-value="sarabun"]         { font-family:'Sarabun',sans-serif; }
    .ql-picker.ql-font .ql-picker-item[data-value="prompt"]          { font-family:'Prompt',sans-serif; }
    .ql-picker.ql-font .ql-picker-item[data-value="kanit"]           { font-family:'Kanit',sans-serif; }
    .ql-picker.ql-font .ql-picker-item[data-value="noto-serif-thai"] { font-family:'Noto Serif Thai',serif; }
    .ql-picker.ql-font .ql-picker-item[data-value="mitr"]            { font-family:'Mitr',sans-serif; }
    .ql-picker.ql-font .ql-picker-item[data-value="charm"]           { font-family:'Charm',cursive; }
    .ql-picker.ql-font .ql-picker-item[data-value="trirong"]         { font-family:'Trirong',serif; }
    .ql-picker.ql-font .ql-picker-item[data-value="monospace"]       { font-family:monospace; }

    .thread-reading {
        font-size: 18px !important;
        line-height: 1.9 !important;
    }
    .thread-reading .ql-editor {
        font-size: inherit !important;
        line-height: inherit !important;
        font-family: inherit !important;
        color: inherit !important;
    }
    .thread-reading .ql-align-center  { text-align:center; }
    .thread-reading .ql-align-right   { text-align:right; }
    .thread-reading .ql-align-justify { text-align:justify; }
    .thread-reading .ql-indent-1 { padding-left:3em; }
    .thread-reading .ql-indent-2 { padding-left:6em; }
    .thread-reading .ql-indent-3 { padding-left:9em; }
    .thread-reading .ql-indent-4 { padding-left:12em; }
    .thread-reading .ql-indent-5 { padding-left:15em; }
    .thread-reading .ql-indent-6 { padding-left:18em; }
    .thread-reading .ql-indent-7 { padding-left:21em; }
    .thread-reading .ql-indent-8 { padding-left:24em; }
    .thread-reading .ql-rindent-1 { padding-right:3em; }
    .thread-reading .ql-rindent-2 { padding-right:6em; }
    .thread-reading .ql-rindent-3 { padding-right:9em; }
    .thread-reading .ql-rindent-4 { padding-right:12em; }
    .thread-reading .ql-rindent-5 { padding-right:15em; }
    .thread-reading .ql-rindent-6 { padding-right:18em; }
    .thread-reading .ql-rindent-7 { padding-right:21em; }
    .thread-reading .ql-rindent-8 { padding-right:24em; }
    .thread-reading h1 { font-size:2em; }
    .thread-reading h2 { font-size:1.5em; }
    .thread-reading h3 { font-size:1.17em; }
    .thread-reading h4 { font-size:1em; }
    .thread-reading h5 { font-size:.83em; }
    .thread-reading h6 { font-size:.67em; }

    .drop-cap {
        float: left;
        font-family: var(--font-decorative);
        font-size: 58px;
        line-height: 72px;
        color: #d4af37;
        font-weight: 700;
        padding: 0 8px 0 0;
    }

    .color-picker-wrap { display:inline-flex; align-items:center; gap:.4rem; vertical-align:middle; }
    .color-hue-slider { -webkit-appearance:none; appearance:none; width:100px; height:10px; border-radius:5px; cursor:pointer; border:1px solid #333; background:linear-gradient(to right,#ff0000,#ff8000,#ffff00,#80ff00,#00ff00,#00ff80,#00ffff,#0080ff,#0000ff,#8000ff,#ff00ff,#ff0080,#ff0000); }
    .color-hue-slider::-webkit-slider-thumb { -webkit-appearance:none; width:14px; height:14px; border-radius:50%; background:#fff; border:2px solid #555; cursor:pointer; }
    .color-hue-slider::-moz-range-thumb { width:14px; height:14px; border-radius:50%; background:#fff; border:2px solid #555; cursor:pointer; }
    .color-hex-input { width:90px; padding:.25rem .4rem; border:1px solid #2a2a2a; border-radius:.35rem; background:#111; color:#e8e6e3; font-size:.8rem; }
    .color-hex-input:focus { outline:1px solid #D4AF37; }
    .color-preview-box { width:22px; height:22px; border-radius:4px; border:1px solid #2a2a2a; flex-shrink:0; }
</style>
