#!/usr/bin/env python3
"""Extract catalogue seed data from the Products design prototype."""

import json
import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parents[2]
SRC = ROOT / "design" / "Keybolts Products.html"
DETAIL = ROOT / "design" / "Keybolts Product Detail.html"
OUT = pathlib.Path(__file__).parent / "catalog.json"


def page_template(html: str) -> str:
    """The real markup is a JSON string inside a bundler script tag."""
    m = re.search(r'<script type="__bundler/template">\s*(".*?")\s*</script>', html, re.S)
    if not m:
        raise SystemExit("template script not found")
    return json.loads(m.group(1))


def js_array(source: str, name: str) -> list[dict]:
    """Parse a `NAME = [ {...}, ... ];` literal into Python dicts."""
    m = re.search(rf"\b{name}\s*=\s*\[(.*?)\n  \];", source, re.S)
    if not m:
        raise SystemExit(f"array {name} not found")
    items = []
    for obj in re.finditer(r"\{([^{}]*)\}", m.group(1)):
        entry = {}
        for k, v in re.findall(r"(\w+):\s*'((?:[^'\\]|\\.)*)'", obj.group(1)):
            entry[k] = v.replace("\\'", "'")
        for k, v in re.findall(r"(\w+):\s*(true|false)", obj.group(1)):
            entry[k] = v == "true"
        if entry:
            items.append(entry)
    return items


def family_of(model: str) -> str:
    """`KB 1700-XL-PVD` -> `KB 1700`. Falls back to the leading token."""
    head = model.split("-")[0].strip()
    return head or model


def brand_map(source: str) -> list[dict]:
    """The current design has no standalone `BRANDS = [...]` literal (the
    prototype changed since this script was written). Brand metadata now
    lives inline as `const brandMeta = { key: { name: '...', ... }, ... };`.
    Fall back to parsing that object so brand data still comes from the
    design rather than being invented.
    """
    m = re.search(r"brandMeta\s*=\s*\{(.*?)\};", source, re.S)
    if not m:
        raise SystemExit("brandMeta not found")
    brands = []
    for bm in re.finditer(r"(\w+):\s*\{([^{}]*)\}", m.group(1)):
        key, inner = bm.group(1), bm.group(2)
        name_m = re.search(r"name:\s*'((?:[^'\\]|\\.)*)'", inner)
        if name_m:
            brands.append({"key": key, "name": name_m.group(1)})
    return brands


def main() -> None:
    source = page_template(SRC.read_text(encoding="utf-8"))
    try:
        brands = js_array(source, "BRANDS")
    except SystemExit:
        brands = brand_map(source)
    data = {
        "brands": brands,
        "categories": js_array(source, "CATALOG_CATS"),
        "finishes": js_array(source, "FINISHES"),
        "products": js_array(source, "CATALOG"),
    }
    for p in data["products"]:
        p["family"] = family_of(p.get("model", ""))
    data["brands"] = [b for b in data["brands"] if b.get("key") != "all"]
    data["categories"] = [c for c in data["categories"] if c.get("key") != "all"]

    detail_src = page_template(DETAIL.read_text(encoding="utf-8"))
    data["specs"] = js_array(detail_src, "SPECS")        # rows of {k, v}
    data["faqs"] = js_array(detail_src, "FAQS")          # rows of {q, a}
    data["policies"] = js_array(detail_src, "POLICIES")  # rows of {title, desc}

    OUT.write_text(json.dumps(data, ensure_ascii=False, indent=2), encoding="utf-8")
    print(f"{OUT.name}: {len(data['products'])} products, "
          f"{len(data['categories'])} categories, {len(data['finishes'])} finishes")


if __name__ == "__main__":
    main()
