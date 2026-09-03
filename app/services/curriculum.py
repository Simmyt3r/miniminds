import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[2]
_catalogue = json.loads((ROOT / "data" / "curriculum.json").read_text(encoding="utf-8"))
PATHS = {item["id"]: item for item in _catalogue["paths"]}
LESSONS = {item["id"]: item for item in _catalogue["lessons"]}
if len(PATHS) != 25 or len(LESSONS) != 1000:
    raise RuntimeError("MiniMinds curriculum catalogue is incomplete.")


def path_for_lesson(lesson_id):
    return next((path for path in PATHS.values() if lesson_id in path["lesson_ids"]), None)
