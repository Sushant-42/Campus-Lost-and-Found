
class APIError(Exception):
    status_code = 400

    def __init__(self, message="An error occurred"):
        super().__init__(message)
        self.message = message


class NotFoundError(APIError):
    status_code = 404

    def __init__(self, message="Resource not found"):
        super().__init__(message)


class ValidationError(APIError):
    status_code = 400

    def __init__(self, message="Invalid request"):
        super().__init__(message)


class ConflictError(APIError):
    status_code = 409

    def __init__(self, message="Conflict"):
        super().__init__(message)


class ServiceUnavailableError(APIError):
    status_code = 503

    def __init__(self, message="Service unavailable"):
        super().__init__(message)