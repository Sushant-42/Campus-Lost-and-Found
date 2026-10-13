from dataclasses import dataclass, field
from typing import List, Optional

@dataclass
class Item:
    item_id: str
    title: str
    description: str
    category: str
    location: str
    item_type: str  # "lost" or "found"
    status: str = "reported"  # reported, claimed, resolved, closed
    student_id: str = ""