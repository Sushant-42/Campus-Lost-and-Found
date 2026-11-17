
from dataclasses import dataclass
from datetime import datetime, timezone


@dataclass
class Claim:
    id: int
    item_id: int
    claimant_id: int
    message: str
    status: str
    created_at: str
    idempotency_key: str

    def as_json(self):
        return {
            "id": self.id,
            "item_id": self.item_id,
            "claimant_id": self.claimant_id,
            "message": self.message,
            "status": self.status
        }


@dataclass
class ClaimInput:
    item_id: int
    claimant_id: int
    message: str
