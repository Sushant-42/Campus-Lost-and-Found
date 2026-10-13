from dataclasses import dataclass


@dataclass
class Order:
    id: int
    item_id: int
    claimant_name: str
    status: str

    def to_dict(self):
        return {
            "id": self.id,
            "item_id": self.item_id,
            "claimant_name": self.claimant_name,
            "status": self.status
        }


@dataclass
class OrderInput:
    item_id: int
    claimant_name: str
    status: str
