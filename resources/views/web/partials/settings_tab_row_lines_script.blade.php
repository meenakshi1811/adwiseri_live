<script>
(function () {
    function drawSettingsTabRowLines() {
        var tablist = document.getElementById('settingsTab');
        if (!tablist) {
            return;
        }

        tablist.style.position = 'relative';
        tablist.querySelectorAll('.settings-tab-row-line').forEach(function (line) {
            line.remove();
        });

        var items = Array.prototype.slice.call(tablist.querySelectorAll('.nav-item'));
        if (!items.length) {
            return;
        }

        items.forEach(function (item) {
            item.classList.remove('settings-tab-row-start', 'settings-tab-row-end');
        });

        var rows = new Map();
        items.forEach(function (item) {
            var top = item.offsetTop;
            if (!rows.has(top)) {
                rows.set(top, []);
            }
            rows.get(top).push(item);
        });

        rows.forEach(function (rowItems) {
            rowItems[0].classList.add('settings-tab-row-start');
            rowItems[rowItems.length - 1].classList.add('settings-tab-row-end');

            var first = rowItems[0];
            var last = rowItems[rowItems.length - 1];
            var line = document.createElement('span');
            line.className = 'settings-tab-row-line';
            line.setAttribute('aria-hidden', 'true');
            line.style.left = first.offsetLeft + 'px';
            line.style.width = (last.offsetLeft + last.offsetWidth - first.offsetLeft) + 'px';
            line.style.top = (first.offsetTop + first.offsetHeight - 2) + 'px';
            tablist.appendChild(line);
        });
    }

    function scheduleDraw() {
        window.requestAnimationFrame(drawSettingsTabRowLines);
    }

    document.addEventListener('DOMContentLoaded', scheduleDraw);
    window.addEventListener('load', scheduleDraw);
    window.addEventListener('resize', scheduleDraw);
    window.drawSettingsTabRowLines = drawSettingsTabRowLines;
})();
</script>
