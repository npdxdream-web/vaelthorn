{{-- Shared Quill helper functions — same core logic used by thread-create.blade.php
     and thread.blade.php's reply editor. Include inside a @push('scripts') block,
     before the page-specific DOMContentLoaded initialization. --}}
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
function initColorPicker(sliderId, hexId, previewId, quill) {
    const slider  = document.getElementById(sliderId);
    const hexEl   = document.getElementById(hexId);
    const preview = document.getElementById(previewId);

    function hslToHex(h) {
        const s = 1, l = 0.5, a = s * Math.min(l, 1 - l);
        const f = n => {
            const k = (n + h / 30) % 12;
            const c = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
            return Math.round(255 * c).toString(16).padStart(2, '0');
        };
        return '#' + f(0) + f(8) + f(4);
    }

    function hexToHue(hex) {
        const r = parseInt(hex.slice(1,3), 16) / 255;
        const g = parseInt(hex.slice(3,5), 16) / 255;
        const b = parseInt(hex.slice(5,7), 16) / 255;
        const max = Math.max(r,g,b), min = Math.min(r,g,b), d = max - min;
        if (d === 0) return 0;
        let h = max === r ? ((g - b) / d + (g < b ? 6 : 0))
              : max === g ? ((b - r) / d + 2)
              :              ((r - g) / d + 4);
        return Math.round(h * 60);
    }

    function applyColor(hexVal) {
        if (preview) preview.style.background = hexVal;
        quill.format('color', hexVal);
    }

    slider.addEventListener('input', () => {
        const hexVal = hslToHex(parseInt(slider.value));
        hexEl.value = hexVal.toUpperCase();
        applyColor(hexVal);
    });

    hexEl.addEventListener('input', () => {
        const val = hexEl.value.trim();
        if (/^#([0-9A-Fa-f]{6})$/.test(val)) {
            slider.value = hexToHue(val);
            applyColor(val);
        }
    });

    hexEl.addEventListener('keydown', e => {
        if (e.key === 'Enter') { e.preventDefault(); hexEl.dispatchEvent(new Event('input')); }
    });
}

// Quill's block-level 'indent' format shifts every wrapped line of the
// paragraph, not just the first — wrong for a Thai-style first-line
// paragraph indent (ย่อหน้า). So Tab here inserts literal non-breaking
// spaces at the cursor instead of applying a block format: it only ever
// affects the first line, and needs no matching display CSS since it's
// plain text content.
// Bails (returns true) when the cursor is inside a list/blockquote/
// code-block, since Tab there should keep doing Quill's own format
// indent (nesting), not insert NBSP text.
function quillIndentAnywhereBindings() {
    var NBSP_CODE = 160;
    var INDENT_UNIT = String.fromCharCode(NBSP_CODE, NBSP_CODE, NBSP_CODE, NBSP_CODE);
    function isNbspOnly(str) {
        for (var i = 0; i < str.length; i++) {
            if (str.charCodeAt(i) !== NBSP_CODE) return false;
        }
        return true;
    }
    function isStructuredFormat(format) {
        return !!(format && (format.list || format.blockquote || format.indent || format['code-block']));
    }
    return {
        'indent-anywhere': {
            key: 9,
            shiftKey: false,
            collapsed: true,
            handler: function (range, context) {
                if (isStructuredFormat(context.format)) return true;
                var lineText = this.quill.getText(range.index - context.offset, context.offset);
                if (!isNbspOnly(lineText)) return true;
                this.quill.insertText(range.index, INDENT_UNIT, Quill.sources.USER);
                this.quill.setSelection(range.index + INDENT_UNIT.length, Quill.sources.SILENT);
                return false;
            },
        },
        'outdent-anywhere': {
            key: 9,
            shiftKey: true,
            collapsed: true,
            handler: function (range, context) {
                if (isStructuredFormat(context.format)) return true;
                var lineStart = range.index - context.offset;
                if (context.offset > 0) {
                    var beforeCursor = this.quill.getText(lineStart, context.offset);
                    if (!isNbspOnly(beforeCursor)) return true;
                    var removeLen = Math.min(INDENT_UNIT.length, beforeCursor.length);
                    this.quill.deleteText(range.index - removeLen, removeLen, Quill.sources.USER);
                    this.quill.setSelection(range.index - removeLen, Quill.sources.SILENT);
                    return false;
                }
                var leading = this.quill.getText(lineStart, INDENT_UNIT.length);
                var leadingCount = 0;
                while (leadingCount < leading.length && leading.charCodeAt(leadingCount) === NBSP_CODE) leadingCount++;
                if (leadingCount === 0) return true;
                this.quill.deleteText(lineStart, leadingCount, Quill.sources.USER);
                this.quill.setSelection(lineStart, Quill.sources.SILENT);
                return false;
            },
        },
    };
}

// Quill 1.3.7 registers several of its own Tab bindings ahead of anything
// passed via `modules.keyboard.bindings` in the constructor — bindings
// always run first-match-wins in registration order, so prepend ours
// directly onto quill.keyboard.bindings[9] after construction.
function installIndentAnywhereBindings(quill) {
    var TAB_KEY = 9;
    var ours = quillIndentAnywhereBindings();
    quill.keyboard.bindings[TAB_KEY] = [ours['indent-anywhere'], ours['outdent-anywhere']]
        .concat(quill.keyboard.bindings[TAB_KEY] || []);
}

// Drop cap is a plain toggleable inline format (like bold/italic), not an
// automatic CSS ::first-letter rule — the user picks the exact paragraph
// by placing the cursor in it and clicking the button.
function registerDropCapFormat() {
    var InlineBlot = Quill.import('blots/inline');
    class DropCap extends InlineBlot {
        static formats() { return true; }
    }
    DropCap.blotName = 'dropcap';
    DropCap.tagName = 'span';
    DropCap.className = 'drop-cap';
    Quill.register(DropCap, true);
}

function dropCapToolbarHandler() {
    var range = this.quill.getSelection();
    if (!range) return;
    var lineInfo = this.quill.getLine(range.index);
    var line = lineInfo[0];
    var offset = lineInfo[1];
    if (!line) return;
    var lineStart = range.index - offset;
    var lineText = this.quill.getText(lineStart, line.length());
    var match = lineText.match(/\S/);
    if (!match) return;
    var charIndex = lineStart + match.index;
    var isActive = !!this.quill.getFormat(charIndex, 1).dropcap;
    this.quill.formatText(charIndex, 1, 'dropcap', !isActive, Quill.sources.USER);
}

// Quill Snow theme's default link/image tooltip desyncs under this site's
// sitewide CSS zoom — plain window.prompt() is a native dialog, unaffected.
function linkToolbarHandler() {
    var range = this.quill.getSelection();
    if (!range) return;
    if (this.quill.getFormat(range).link) {
        this.quill.format('link', false, Quill.sources.USER);
        return;
    }
    if (range.length === 0) return;
    var url = window.prompt('ใส่ URL ลิงก์:');
    if (!url) return;
    this.quill.format('link', url, Quill.sources.USER);
}

function imageToolbarHandler() {
    var range = this.quill.getSelection(true);
    var url = window.prompt('ใส่ URL รูปภาพ:');
    if (!url) return;
    this.quill.insertEmbed(range ? range.index : this.quill.getLength(), 'image', url, Quill.sources.USER);
}

// Right-indent mirrors Quill's own built-in left `indent` format exactly.
function registerIndentRightFormat() {
    var Parchment = Quill.import('parchment');
    class IndentRightAttributor extends Parchment.Attributor.Class {
        add(node, value) {
            if (value === '+1' || value === '-1') {
                var current = this.value(node) || 0;
                value = value === '+1' ? current + 1 : current - 1;
            }
            if (value === 0) {
                this.remove(node);
                return true;
            }
            return super.add(node, value);
        }
        canAdd(node, value) {
            return super.canAdd(node, value) || super.canAdd(node, parseInt(value));
        }
        value(node) {
            return parseInt(super.value(node)) || undefined;
        }
    }
    var IndentRight = new IndentRightAttributor('indentright', 'ql-rindent', {
        scope: Parchment.Scope.BLOCK,
        whitelist: [1, 2, 3, 4, 5, 6, 7, 8],
    });
    Quill.register({ 'formats/indentright': IndentRight }, true);
}

// Explicit px sizes instead of the semantic H1/H2/H3 header dropdown.
function registerSizeFormat() {
    var SizeStyle = Quill.import('attributors/style/size');
    SizeStyle.whitelist = ['12px', '14px', '16px', '18px', '20px', '24px', '28px', '32px', '40px', '48px'];
    Quill.register(SizeStyle, true);
}
</script>
