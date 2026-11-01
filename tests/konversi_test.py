"""Test tools/elementor-ke-orbit/konversi.py. Jalankan: python3 -I tests/konversi_test.py"""
import json
import os
import sys

sys.path.insert(0, os.path.join(os.path.dirname(os.path.abspath(__file__)), '..', 'tools', 'elementor-ke-orbit'))
import konversi  # noqa: E402

gagal = 0


def t(name, expected, actual):
    global gagal
    if expected == actual:
        print('  ok  ', name)
    else:
        gagal += 1
        print('  GAGAL', name, '\n    harap:', repr(expected), '\n    dapat:', repr(actual))


def w(kind, **settings):
    return {'elType': 'widget', 'widgetType': kind, 'settings': settings, 'elements': []}


def post(elements, slug='jasa-uji'):
    return {'post': {'id': 1, 'slug': slug}, 'meta': {'_elementor_data': json.dumps([{'elType': 'container', 'elements': elements}]), '_wp_page_template': 'default', '_elementor_edit_mode': 'builder'}}


seed = konversi.convert(post([
    w('image', image={'id': 55, 'url': 'x.jpg'}),
    w('heading', title='JUDUL UTAMA', header_size='h1'),
    w('text-editor', editor='<p><span style="font-weight:400">Lede <b>tebal</b>.</span></p><div class="intersub-subtitle-bar" style="display:none"> </div>'),
    w('heading', title='Sub judul'),
    w('text-editor', editor='<p>Isi&nbsp;satu &amp; dua.</p><ul><li>a</li><li><a href="/x/" style="c">b</a></li></ul>'),
    w('image', image={'id': 77}),
    w('progress', title='Ads', percent={'size': 25}),
    w('button', text='Lihat', link={'url': '/study-case/'}),
    w('heading', title='INI JAWABAN YANG KAMU CARI?'),
    {'elType': 'widget', 'widgetType': 'nested-accordion', 'settings': {'items': [{'item_title': 'Tanya 1?'}]}, 'elements': [{'elType': 'container', 'elements': [w('text-editor', editor='<p>Jawab 1.</p>')]}]},
]), 'layanan')
m = seed['meta']
t('H1', 'JUDUL UTAMA', m['mea_h1'])
t('lede tanpa tag', 'Lede tebal.', m['mea_lede'])
t('foto hero dari gambar sebelum H1', 55, m['mea_foto_hero_id'])
t('isi bersih', '<h2>Sub judul</h2>\n<p>Isi satu &amp; dua.</p>\n<ul><li>a</li><li><a href="/x/">b</a></li></ul>\n<p>[mea-gambar 77]</p>\n<p>Ads: 25%</p>\n<p><a href="/study-case/">Lihat</a></p>', m['mea_isi'])
t('accordion jadi FAQ', [{'tanya': 'Tanya 1?', 'jawab': 'Jawab 1.'}], m['mea_faq'])
t('judul section FAQ lama dibuang', False, 'JAWABAN' in m['mea_isi'])

html_page = '''<!DOCTYPE html><html><head><title>T</title><style>.a{}</style></head><body>
<section><p class="eyebrow">PARTNER RESMI</p><h1>Judul HTML</h1><p>Pembuka.</p>
<div class="logos"><span>Detik</span><span>Kontan</span></div>
<div><b>21X</b>ROAS</div>
<h2>FAQ</h2><h3>Apa itu?</h3><p>Itu jawaban.</p>
<a href="/klik/x" style="position: fixed; right: 0">Konsultasi</a>
<script type="application/ld+json">{"@type":"FAQPage","mainEntity":[{"@type":"Question","name":"Apa itu?","acceptedAnswer":{"@type":"Answer","text":"Itu jawaban."}}]}</script>
<script>alert(1)</script></section></body></html>'''
seed = konversi.convert(post([w('html', html=html_page)]), 'layanan')
m = seed['meta']
t('label hero', 'PARTNER RESMI', m['mea_label_hero'])
t('H1 dari widget HTML', 'Judul HTML', m['mea_h1'])
t('FAQ dari JSON-LD', [{'tanya': 'Apa itu?', 'jawab': 'Itu jawaban.'}], m['mea_faq'])
t('isi: span dipisah, teks lepas jadi paragraf, salinan FAQ dan script dibuang, tombol melayang jadi link',
  '<p>Detik Kontan</p>\n<p><strong>21X</strong> ROAS</p>\n<p><a href="/klik/x">Konsultasi</a></p>', m['mea_isi'])

seed = konversi.convert(post([
    w('heading', title='Pembuka sebelum H1'),
    w('text-editor', editor='<p>Teks awal.</p>'),
    w('heading', title='H1 di tengah', header_size='h1'),
    w('text-editor', editor='<p>Sesudah.</p>'),
    w('heading', title='H1 kedua', header_size='h1'),
]), 'layanan')
m = seed['meta']
t('H1 di tengah: tanpa lede, urutan isi tetap', ('', '<h2>Pembuka sebelum H1</h2>\n<p>Teks awal.</p>\n<p>Sesudah.</p>\n<h2>H1 kedua</h2>'), (m['mea_lede'], m['mea_isi']))
t('judul pendek dari peta slug', 'TikTok Ads', konversi.convert(post([w('heading', title='X', header_size='h1')], 'jasa-tiktok-ads'), 'layanan')['meta']['mea_judul_pendek'])

seed = konversi.convert(post([
    w('text-editor', editor='<p>Sebelum.</p>'),
    w('heading', title='H1 lama', header_size='h1'),
    w('text-editor', editor='<p>Sesudah.</p>'),
], 'jasa-live-streaming'), 'layanan')
m = seed['meta']
t('H1 baru dari peta; H1 lama jadi H2 di posisinya', ('Jasa Live Streaming Shopee dan TikTok Shop dari Studio Bandung', '', '<p>Sebelum.</p>\n<h2>H1 lama</h2>\n<p>Sesudah.</p>'), (m['mea_h1'], m['mea_lede'], m['mea_isi']))

seed = konversi.convert(post([
    w('heading', title='X', header_size='h1'),
    w('text-editor', editor='<p>Pembuka.</p><p>Rp30M+</p><p>Budget iklan yang sudah dikelola</p><p>Jaringan 4.000+ affiliator kami.</p>'),
]), 'layanan')
t('angka resmi diganti, teks lain tetap', '<p>USD 4-5 juta</p>\n<p>Budget iklan TikTok yang dikelola (2026)</p>\n<p>Jaringan 12.000 kreator MEA kami.</p>', seed['meta']['mea_isi'])

print('\n%s' % ('semua lolos' if not gagal else '%d gagal' % gagal))
sys.exit(1 if gagal else 0)
