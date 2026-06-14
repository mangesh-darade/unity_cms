"""Generate web-optimized gallery images from Akshay lab source photos."""
from pathlib import Path
from PIL import Image, ImageEnhance, ImageOps

ROOT = Path(r"c:\wamp64\www\unity_cms\images")
OUT = ROOT / "gallery" / "web"
OUT.mkdir(parents=True, exist_ok=True)

# slug -> (source relative to images/, title for log)
ITEMS = [
    ("rate-card", "akshay_gallery_rate_card.jpg"),
    ("signboard", "akshay_gallery_signboard.jpg"),
    ("blood-collection", "akshay_gallery_blood_collection.jpg"),
    ("collection-desk", "akshay_gallery_collection_desk.jpg"),
    ("sample-processing", "akshay_gallery_sample_processing.jpg"),
    ("report-workstation", "akshay_gallery_report_workstation.jpg"),
    ("serology-reagents", "akshay_gallery_serology_reagents.jpg"),
    ("stem-cell-display", "akshay_gallery_stem_cell.jpg"),
    ("staff-certificate", "akshay_gallery_staff_certificate.jpg"),
    ("hematology-analyzer", "akshay_equip_sysmex_xq320.jpg"),
    ("biochemistry-analyzer", "akshay_equip_orbit_smart7.jpg"),
    ("centrifuge", "akshay_equip_remi_centrifuge.jpg"),
    ("microscope", "akshay_equip_labomed_microscope.jpg"),
]

TARGET_W = 960
TARGET_H = 720  # 4:3 landscape tile for uniform grid


def smart_cover(img: Image.Image, tw: int, th: int) -> Image.Image:
    img = ImageOps.exif_transpose(img)
    if img.mode not in ("RGB", "L"):
        img = img.convert("RGB")
    src_w, src_h = img.size
    scale = max(tw / src_w, th / src_h)
    new_w = int(src_w * scale)
    new_h = int(src_h * scale)
    resized = img.resize((new_w, new_h), Image.Resampling.LANCZOS)
    left = (new_w - tw) // 2
    top = (new_h - th) // 2
    return resized.crop((left, top, left + tw, top + th))


def enhance(img: Image.Image) -> Image.Image:
    img = ImageEnhance.Contrast(img).enhance(1.06)
    img = ImageEnhance.Color(img).enhance(1.04)
    img = ImageEnhance.Sharpness(img).enhance(1.08)
    return img


def main() -> None:
    manifest = []
    for slug, src_name in ITEMS:
        src = ROOT / src_name
        if not src.exists():
            print(f"MISSING: {src}")
            continue
        img = Image.open(src)
        out = smart_cover(img, TARGET_W, TARGET_H)
        out = enhance(out)
        dest = OUT / f"{slug}.jpg"
        out.save(dest, "JPEG", quality=88, optimize=True, progressive=True)
        rel = f"images/gallery/web/{slug}.jpg"
        manifest.append((slug, rel, src_name))
        print(f"OK {rel} ({dest.stat().st_size // 1024} KB)")

    print(f"\nGenerated {len(manifest)} gallery images.")
    return manifest


if __name__ == "__main__":
    main()
