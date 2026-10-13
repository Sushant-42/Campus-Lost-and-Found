class APIError(Exception):
    def __init__(self, status: int, error_type: str, title: str, detail: str, retry_after: int = None, errors: list = None):
        self.status = status
        self.error_type = error_type
        self.title = title
        self.detail = detail
        self.retry_after = retry_after
        self.errors = errors or []
        super().__init__(detail)

def problem_response(error: APIError):
    body = {
        "type": f"https://campuseats.local/errors/{error.error_type}",
        "title": error.title,
        "status": error.status,
        "detail": error.detail
    }
    if error.errors:
        body["errors"] = error.errors
    return body