from typing import Dict, Optional
from .models import Item

items_store: Dict[str, Item] = {}

def save_item(item: Item) -> None:
    items_store[item.item_id] = item

def get_item(item_id: str) -> Optional[Item]:
    return items_store.get(item_id)

def get_all_items() -> list[Item]:
    return list(items_store.values())

def delete_item(item_id: str) -> None:
    items_store.pop(item_id, None)