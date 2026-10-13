class APIError(Exception):
    def __init__(self, status, error_type, title, detail, errors=None, retry_after=None):
        super().__init__(detail)
        self.status = status
        self.error_type = error_type
        self.title = title
        self.detail = detail
        self.errors = errors
        self.retry_after = retry_after

def problem_response(error: APIError):
    problem = {
        "type": f"https://campuslostandfound.local/errors/{error.error_type}",
        "title": error.title,
        "status": error.status,
        "detail": error.detail
    }
    if error.errors is not None:
        problem["errors"] = error.errors
    return problem