
from models import Claim
from datetime import datetime, timezone


class ClaimStore:
    def __init__(self):
        self.claims = {}
        self.idempotency_records = {}
        self.next_id = 1

    def create(self, item_id, claimant_id, message, idempotency_key):
        # Return the original claim if this key was already used.
        if idempotency_key in self.idempotency_records:
            claim_id = self.idempotency_records[idempotency_key]
            return self.claims[claim_id], False

        claim = Claim(
            id=self.next_id,
            item_id=item_id,
            claimant_id=claimant_id,
            message=message,
            status="pending",
            created_at=datetime.now(timezone.utc).isoformat(),
            idempotency_key=idempotency_key
        )

        self.claims[claim.id] = claim
        self.idempotency_records[idempotency_key] = claim.id
        self.next_id += 1

        return claim, True

    def get(self, claim_id):
        return self.claims.get(claim_id)

    def list(self, status=None):
        claims = list(self.claims.values())

        if status:
            claims = [claim for claim in claims if claim.status == status]

        return claims

    def cancel(self, claim_id):
        claim = self.get(claim_id)

        if claim is None:
            return None

        if claim.status != "pending":
            return False

        claim.status = "cancelled"
        return claim


store = ClaimStore()
