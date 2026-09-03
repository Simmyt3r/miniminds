"""Convert the supplied MiniMinds workbook to the runtime curriculum catalogue.

Uses only the Python standard library so the import stays reproducible in deploy
and CI environments without an Excel dependency.
"""
import json
import re
import sys
import zipfile
import xml.etree.ElementTree as ET
from pathlib import Path

NS = {"x": "http://schemas.openxmlformats.org/spreadsheetml/2006/main"}
COLUMNS = [
    "id", "category", "subtopic", "title", "age_min", "age_max", "difficulty",
    "lesson_type", "order", "xp", "points", "minutes", "objectives", "content",
    "gamification", "character", "setting", "prerequisites", "parent_tip", "keywords",
]


def cell_value(cell):
    inline = cell.find("x:is", NS)
    if inline is not None:
        return "".join(inline.itertext())
    value = cell.find("x:v", NS)
    return value.text if value is not None else ""


def row_values(sheet):
    for row in sheet.findall(".//x:sheetData/x:row", NS):
        values = {}
        for cell in row.findall("x:c", NS):
            column = re.match(r"[A-Z]+", cell.get("r")).group(0)
            values[column] = cell_value(cell)
        yield values


def read_sheet(workbook, number):
    return ET.fromstring(workbook.read(f"xl/worksheets/sheet{number}.xml"))


def main(source, destination):
    with zipfile.ZipFile(source) as workbook:
        lesson_rows = list(row_values(read_sheet(workbook, 2)))[1:]
        mapping_rows = list(row_values(read_sheet(workbook, 4)))[2:]

    paths = []
    for row in mapping_rows:
        start, end = (int(value) for value in row["H"].split("-"))
        paths.append({
            "id": int(row["A"]), "title": row["B"], "category": row["C"],
            "description": row["D"], "difficulty": row["E"],
            "age_min": int(row["F"]), "age_max": int(row["G"]),
            "lesson_ids": list(range(start, end + 1)),
        })

    lessons = []
    for row in lesson_rows:
        item = {key: row.get(chr(65 + index), "") for index, key in enumerate(COLUMNS)}
        for key in ("id", "age_min", "age_max", "order", "xp", "points", "minutes"):
            item[key] = int(item[key])
        item["objectives"] = [part.strip() for part in item["objectives"].split("•") if part.strip()]
        lessons.append(item)

    if len(lessons) != 1000 or len(paths) != 25:
        raise ValueError(f"Expected 1,000 lessons and 25 paths; found {len(lessons)} and {len(paths)}")
    Path(destination).write_text(json.dumps({"paths": paths, "lessons": lessons}, ensure_ascii=False, indent=2) + "\n")


if __name__ == "__main__":
    root = Path(__file__).resolve().parents[1]
    main(Path(sys.argv[1]) if len(sys.argv) > 1 else root / "MiniMinds_1000_Gamified_Lessons_Assessments.xlsx",
         Path(sys.argv[2]) if len(sys.argv) > 2 else root / "data" / "curriculum.json")
