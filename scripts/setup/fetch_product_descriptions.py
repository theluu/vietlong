#!/usr/bin/env python3
"""Lấy mô tả và ảnh sản phẩm từ site cũ keybolts.com.vn về một file JSON.

CSV chỉ có tên, mã, danh mục và thuộc tính — không mô tả, và 153/192 dòng
không có ảnh nào. Cả hai đều nằm trên trang gốc ở `product_url`: mô tả trong
`div.content-descr-text`, ảnh trong slider phía trên khối sản phẩm cùng loại.

Tách khỏi script import PHP có chủ đích: cào 192 trang mất vài phút và phụ
thuộc mạng, nên nó chạy một lần ra file, còn import đọc file đó. Chạy lại
import không phải cào lại, và mất mạng giữa chừng không làm hỏng dữ liệu.

Chạy: python3 scripts/setup/fetch_product_descriptions.py [--limit N]
Kết quả: docs/product-descriptions.json
"""

import csv
import html
import json
import pathlib
import re
import sys
import time
import urllib.request

ROOT = pathlib.Path(__file__).resolve().parents[2]
OUT = ROOT / "docs" / "product-descriptions.json"

# Chỉ giữ những thẻ trình soạn thảo của site mới cho phép; phần còn lại của
# trang cũ đầy style inline và font-family rác không đáng mang sang.
KEEP = {"p", "br", "ul", "ol", "li", "strong", "b", "em", "i", "h2", "h3", "h4"}


def clean(fragment: str) -> str:
    """HTML rút gọn: bỏ thuộc tính, bỏ thẻ lạ, bỏ đoạn rỗng."""
    fragment = re.sub(r"<(script|style)[^>]*>.*?</\1>", "", fragment, flags=re.S)
    fragment = re.sub(r"<!--.*?-->", "", fragment, flags=re.S)

    def tag(match: re.Match) -> str:
        closing, name = match.group(1), match.group(2).lower()
        if name not in KEEP:
            return ""
        return f"<{closing}{name}>"

    fragment = re.sub(r"<(/?)([a-zA-Z0-9]+)[^>]*>", tag, fragment)
    fragment = html.unescape(fragment)
    fragment = fragment.replace("\xa0", " ")
    # Đoạn chỉ chứa khoảng trắng là tàn dư của thẻ vừa bị bỏ.
    fragment = re.sub(r"<p>\s*</p>", "", fragment)
    fragment = re.sub(r"[ \t]+", " ", fragment)
    fragment = re.sub(r"\n\s*\n+", "\n", fragment)
    return fragment.strip()


def scrape(url: str) -> dict:
    """Mô tả và ảnh gallery của một sản phẩm trên site cũ."""
    request = urllib.request.Request(url, headers={"User-Agent": "keybolts-migration/1.0"})
    with urllib.request.urlopen(request, timeout=30) as response:
        page = response.read().decode("utf-8", errors="ignore")

    body = None
    match = re.search(
        r'<div[^>]*class="content-descr-text"[^>]*>(.*?)</div>\s*(?:<div|</div>)',
        page,
        re.S,
    )
    if match:
        candidate = clean(match.group(1))
        # Dưới 40 ký tự là khung rỗng chứ không phải mô tả.
        if len(re.sub(r"<[^>]+>", "", candidate)) >= 40:
            body = candidate

    # Ảnh của chính sản phẩm nằm trong slider phía trên; `product-image` bắt đầu
    # lưới "sản phẩm cùng loại", nên cắt ở đó thay vì lấy hết mọi thẻ img.
    cutoff = page.find('class="product-image"')
    gallery_html = page[:cutoff] if cutoff != -1 else page
    images = list(dict.fromkeys(
        re.findall(r'<img[^>]+src="(https?://[^"]*sites/default/files/[^"]+)"', gallery_html)
    ))

    return {"body": body, "images": images}


def main() -> None:
    limit = None
    if "--limit" in sys.argv:
        limit = int(sys.argv[sys.argv.index("--limit") + 1])

    with open(ROOT / "docs" / "products.csv", encoding="utf-8-sig") as handle:
        rows = list(csv.DictReader(handle))

    # Nhiều dòng chia nhau một URL; cào một lần cho mỗi URL.
    urls = []
    for row in rows:
        url = row["product_url"].strip()
        if url and url not in urls:
            urls.append(url)
    if limit:
        urls = urls[:limit]

    found = json.loads(OUT.read_text()) if OUT.exists() else {}
    missing = 0
    for i, url in enumerate(urls, 1):
        if url in found:
            continue
        try:
            data = scrape(url)
        except Exception as error:  # noqa: BLE001 — một trang hỏng không được dừng cả mẻ
            print(f"  ! {url} — {error}")
            missing += 1
            continue
        if data["body"] or data["images"]:
            found[url] = data
        else:
            missing += 1
            print(f"  – không có gì lấy được: {url}")
        if i % 20 == 0:
            print(f"  {i}/{len(urls)}…")
            OUT.write_text(json.dumps(found, ensure_ascii=False, indent=1))
        time.sleep(0.3)

    OUT.write_text(json.dumps(found, ensure_ascii=False, indent=1))
    with_body = sum(1 for v in found.values() if v.get("body"))
    with_images = sum(1 for v in found.values() if v.get("images"))
    print(
        f"{len(found)}/{len(urls)} trang lấy được: {with_body} có mô tả, "
        f"{with_images} có ảnh. {missing} trang hỏng hoặc trống. → {OUT}"
    )


if __name__ == "__main__":
    main()
