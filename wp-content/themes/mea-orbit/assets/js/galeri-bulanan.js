/* Galeri bulanan studi kasus (template-parts/studi-kasus/galeri.php). */
(function () {
	var b = matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', each = function (l, f) { [].forEach.call(l, f); };
	each(document.querySelectorAll('[data-bulanan]'), function (root) {
		var list = root.querySelector('[role=tablist]'), tabs = list.children,
			panels = root.querySelectorAll('[role=tabpanel]'),
			track = root.querySelector('.bulanan-track'),
			arah = root.querySelectorAll('[data-arah]'),
			n = tabs.length, cur = 0, timer;
		function go(i, focus, move) {
			cur = i = Math.max(0, Math.min(n - 1, i));
			for (var k = 0; k < n; k++) {
				tabs[k].setAttribute('aria-selected', k === i);
				tabs[k].tabIndex = panels[k].tabIndex = k === i ? 0 : -1;
			}
			arah[0].disabled = !i;
			arah[1].disabled = i === n - 1;
			var t = tabs[i], x = t.offsetLeft;
			if (x < list.scrollLeft || x + t.offsetWidth > list.scrollLeft + list.clientWidth) list.scrollTo({ left: x - (list.clientWidth - t.offsetWidth) / 2, behavior: b });
			if (move) track.scrollTo({ left: panels[i].offsetLeft, behavior: b });
			if (focus) t.focus();
		}
		each(tabs, function (t, i) {
			t.onclick = function (e) { e.preventDefault(); go(i, 0, 1); };
		});
		each(arah, function (a) {
			a.onclick = function () { go(cur + +a.dataset.arah, 0, 1); };
		});
		track.addEventListener('scroll', function () {
			clearTimeout(timer);
			timer = setTimeout(function () {
				var i = Math.round(track.scrollLeft / track.clientWidth);
				if (i !== cur) go(i);
			}, 120);
		}, { passive: true });
		root.onkeydown = function (e) {
			var tab = e.target.getAttribute('role') === 'tab',
				d = { ArrowLeft: -1, ArrowRight: 1, Home: tab && -n, End: tab && n }[e.key];
			if (!d || e.altKey || e.ctrlKey || e.metaKey || e.target.tagName === 'VIDEO') return;
			e.preventDefault();
			go(cur + d, tab, 1);
		};
		var h = [].indexOf.call(panels, document.getElementById(location.hash.slice(1)));
		go(Math.max(h, 0), 0, h > 0);
	});
})();
