from models import Order


class OrderStore:
    def __init__(self):
        self.orders = {}
        self.next_id = 1

    def get_all(self):
        return list(self.orders.values())

    def get(self, order_id):
        return self.orders.get(order_id)

    def create(self, item_id, claimant_name, status):
        order = Order(
            id=self.next_id,
            item_id=item_id,
            claimant_name=claimant_name,
            status=status
        )

        self.orders[self.next_id] = order
        self.next_id += 1

        return order

    def delete(self, order_id):
        if order_id not in self.orders:
            return False

        del self.orders[order_id]
        return True
