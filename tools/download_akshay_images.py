import subprocess
import sys
from pathlib import Path

FILES = [
    ("1VxIDo3T8Ud3l0BwYY03KEK1x2yMVMiEU", "IMG-20260227-WA0001.jpg"),
    ("1RnyBly_sRkiPnXR5JFoX2fRcaKUM_93h", "IMG-20260425-WA0007.jpg"),
    ("1vStzAkLcXVSHCPtVi67q2oE7fG-xQ1Zn", "IMG-20260614-WA0000.jpg"),
    ("1iuomo97LJskFiGWDGr52IKmy0LrjSly9", "IMG-20260614-WA0001.jpg"),
    ("1UfpV_4mggp69tCIenxf423VK3MqE-cOv", "IMG-20260614-WA0002.jpg"),
    ("1RJCRajrPkfGR2h3iDiF09O3UXTlKdyjo", "IMG-20260614-WA0003.jpg"),
    ("1JK1rUad1x2K6fbKIto5JjHf3Abu9vwTo", "IMG-20260614-WA0004.jpg"),
    ("1mzajfirF5t5VeujEkXXBbP-oMhJSrMBD", "IMG-20260614-WA0005.jpg"),
    ("1x2eyFgtERZYOQC9ElvH65XrWqZS8uR2C", "IMG-20260614-WA0006.jpg"),
    ("1aPyYrTPu9ZGWw_VjTC67w2e5fnfaNgga", "IMG-20260614-WA0008.jpg"),
    ("1R72FflDJfH7W1tF0QurIu22UB811DtWj", "IMG-20260614-WA0009.jpg"),
    ("1hOsWt5SiCfHpjlli6u7tj-scc0fidOmB", "IMG-20260614-WA0010.jpg"),
    ("1RDzM2eYVa1wbBrXTnLQ0L7d-rQ98Rztt", "IMG-20260614-WA0011.jpg"),
    ("1bgcNfEgntinXY1YZtF8wJOk6CYAql9C-", "IMG-20260614-WA0012.jpg"),
]

OUT = Path(r"c:\wamp64\www\unity_cms\images\akshay_lab")
OUT.mkdir(parents=True, exist_ok=True)

for file_id, name in FILES:
    dest = OUT / name
    if dest.exists() and dest.stat().st_size > 1000:
        print(f"skip {name}")
        continue
    print(f"download {name}...")
    subprocess.run(
        [sys.executable, "-m", "gdown", f"https://drive.google.com/uc?id={file_id}", "-O", str(dest)],
        check=True,
    )

print("done")
