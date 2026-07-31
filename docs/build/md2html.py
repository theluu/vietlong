#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""Chuyển kế hoạch triển khai (Markdown) sang HTML in ấn A4."""
import html
import re
import sys


def inline(t):
    t = html.escape(t)
    t = t.replace('&lt;br&gt;', '<br>')
    t = re.sub(r'`([^`]+)`', r'<code>\1</code>', t)
    t = re.sub(r'\*\*([^*]+)\*\*', r'<strong>\1</strong>', t)
    t = re.sub(r'(?<!\*)\*([^*\n]+)\*(?!\*)', r'<em>\1</em>', t)
    t = re.sub(r'\[([^\]]+)\]\(([^)]+)\)', r'<a href="\2">\1</a>', t)
    return t


def cells(line):
    return [c.strip() for c in line.strip().strip('|').split('|')]


def convert(md):
    lines = md.split('\n')
    out, i, n = [], 0, len(lines)

    while i < n:
        ln = lines[i]

        # code block
        if ln.startswith('```'):
            i += 1
            buf = []
            while i < n and not lines[i].startswith('```'):
                buf.append(html.escape(lines[i]))
                i += 1
            i += 1
            out.append('<pre>' + '\n'.join(buf) + '</pre>')
            continue

        # table
        if ln.startswith('|') and i + 1 < n and re.match(r'^\|[\s:|-]+\|$', lines[i + 1]):
            head = cells(ln)
            i += 2
            body = []
            while i < n and lines[i].startswith('|'):
                body.append(cells(lines[i]))
                i += 1
            t = ['<table><thead><tr>']
            t += [f'<th>{inline(c)}</th>' for c in head]
            t.append('</tr></thead><tbody>')
            for row in body:
                t.append('<tr>' + ''.join(f'<td>{inline(c)}</td>' for c in row) + '</tr>')
            t.append('</tbody></table>')
            out.append(''.join(t))
            continue

        # headings
        m = re.match(r'^(#{1,4})\s+(.*)$', ln)
        if m:
            lvl, txt = len(m.group(1)), m.group(2)
            out.append(f'<h{lvl}>{inline(txt)}</h{lvl}>')
            i += 1
            continue

        # horizontal rule
        if re.match(r'^---+$', ln.strip()):
            out.append('<hr>')
            i += 1
            continue

        # blockquote
        if ln.startswith('> '):
            buf = []
            while i < n and lines[i].startswith('> '):
                buf.append(lines[i][2:])
                i += 1
            out.append('<blockquote>' + inline(' '.join(buf)) + '</blockquote>')
            continue

        # list (checkbox or bullet)
        if re.match(r'^[-*]\s+', ln):
            items, checklist = [], False
            while i < n and re.match(r'^[-*]\s+', lines[i]):
                txt = re.sub(r'^[-*]\s+', '', lines[i])
                if txt.startswith('[ ] '):
                    checklist = True
                    items.append(f'<li class="cb">{inline(txt[4:])}</li>')
                else:
                    items.append(f'<li>{inline(txt)}</li>')
                i += 1
            cls = ' class="check"' if checklist else ''
            out.append(f'<ul{cls}>' + ''.join(items) + '</ul>')
            continue

        # numbered list
        if re.match(r'^\d+\.\s+', ln):
            items = []
            while i < n and re.match(r'^\d+\.\s+', lines[i]):
                items.append('<li>' + inline(re.sub(r'^\d+\.\s+', '', lines[i])) + '</li>')
                i += 1
            out.append('<ol>' + ''.join(items) + '</ol>')
            continue

        # blank
        if not ln.strip():
            i += 1
            continue

        # paragraph
        buf = []
        while i < n and lines[i].strip() and not re.match(r'^(#|\||```|---+$|[-*]\s|\d+\.\s|> )', lines[i]):
            buf.append(lines[i])
            i += 1
        if buf:
            out.append('<p>' + inline(' '.join(buf)) + '</p>')

    # bỏ đường kẻ ngang đứng ngay trước tiêu đề (tránh sinh trang trắng)
    cleaned = []
    for k, el in enumerate(out):
        nxt = out[k + 1] if k + 1 < len(out) else ''
        if el == '<hr>' and re.match(r'^<h[12]>', nxt):
            continue
        cleaned.append(el)
    return '\n'.join(cleaned)


if __name__ == '__main__':
    src, dst, tpl = sys.argv[1], sys.argv[2], sys.argv[3]
    md = open(src, encoding='utf-8').read()
    body = convert(md)
    shell = open(tpl, encoding='utf-8').read()
    open(dst, 'w', encoding='utf-8').write(shell.replace('<!--CONTENT-->', body))
    print('written', dst)
