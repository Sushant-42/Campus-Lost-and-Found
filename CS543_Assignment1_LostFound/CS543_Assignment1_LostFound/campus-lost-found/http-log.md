# HTTP Request and Response Log

## Environment Details

- **Date:** October 13, 2026
- **Time recorded:** 15:32 (local time, IST)
- **Operating System:** Microsoft Windows [Version 10.0.26200.9457]
- **curl version:** curl 8.21.0 (Windows)
- **API:** JSONPlaceholder
- **Base URL:** https://jsonplaceholder.typicode.com
- **Method used:** GET
- **Purpose:** To observe actual HTTP requests, status codes, response headers, and JSON response bodies using curl.

All five requests were made to the public, read-only JSONPlaceholder API. The saved evidence files are located in the `evidence/` directory.

---

## Request 1 — Retrieve a User

### Command

```cmd
curl.exe -i -v https://jsonplaceholder.typicode.com/users/1
```

### Request

```http
GET /users/1 HTTP/1.1
Host: jsonplaceholder.typicode.com
User-Agent: curl/8.21.0
Accept: */*
```

### Response

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
Content-Length: 509
Connection: keep-alive
Cache-Control: max-age=43200
```

Response body:

```json
{
  "id": 1,
  "name": "Leanne Graham",
  "username": "Bret",
  "email": "Sincere@april.biz",
  "address": {
    "street": "Kulas Light",
    "suite": "Apt. 556",
    "city": "Gwenborough",
    "zipcode": "92998-3874",
    "geo": {
      "lat": "-37.3159",
      "lng": "81.1496"
    }
  },
  "phone": "1-770-736-8031 x56442",
  "website": "hildegard.org",
  "company": {
    "name": "Romaguera-Crona",
    "catchPhrase": "Multi-layered client-server neural-net",
    "bs": "harness real-time e-markets"
  }
}
```

### Observation

The server returned **200 OK**, meaning the request succeeded. The `Content-Type` header identifies the response as JSON encoded using UTF-8. The response contains the user record with ID 1.

**Evidence file:** `evidence/01-get-user.txt`

---

## Request 2 — Retrieve a Single Post

### Command

```cmd
curl.exe -i -v https://jsonplaceholder.typicode.com/posts/1
```

### Request

```http
GET /posts/1 HTTP/1.1
Host: jsonplaceholder.typicode.com
User-Agent: curl/8.21.0
Accept: */*
```

### Response

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
Content-Length: 292
Connection: keep-alive
Cache-Control: max-age=43200
```

Response body:

```json
{
  "userId": 1,
  "id": 1,
  "title": "sunt aut facere repellat provident occaecati excepturi optio reprehenderit",
  "body": "quia et suscipit\nsuscipit recusandae consequuntur expedita et cum\nreprehenderit molestiae ut ut quas totam\nnostrum rerum est autem sunt rem eveniet architecto"
}
```

### Observation

The server returned **200 OK**, indicating that the requested post was retrieved successfully. The response uses `application/json; charset=utf-8`, meaning the representation is JSON text encoded using UTF-8.

**Evidence file:** `evidence/02-get-post.txt`

---

## Request 3 — Retrieve Posts Filtered by User ID

### Command

```cmd
curl.exe -i -v "https://jsonplaceholder.typicode.com/posts?userId=1"
```

### Request

```http
GET /posts?userId=1 HTTP/1.1
Host: jsonplaceholder.typicode.com
User-Agent: curl/8.21.0
Accept: */*
```

### Response

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
Transfer-Encoding: chunked
Connection: keep-alive
Cache-Control: max-age=43200
```

Response body:

```json
[
  {
    "userId": 1,
    "id": 1,
    "title": "sunt aut facere repellat provident occaecati excepturi optio reprehenderit",
    "body": "quia et suscipit\nsuscipit recusandae consequuntur expedita et cum\nreprehenderit molestiae ut ut quas totam\nnostrum rerum est autem sunt rem eveniet architecto"
  },
  {
    "userId": 1,
    "id": 2,
    "title": "qui est esse",
    "body": "est rerum tempore vitae\nsequi sint nihil reprehenderit dolor beatae ea dolores neque\nfugiat blanditiis voluptate porro vel nihil molestiae ut reiciendis\nqui aperiam non debitis possimus qui neque nisi nulla"
  },
  {
    "userId": 1,
    "id": 3,
    "title": "ea molestias quasi exercitationem repellat qui ipsa sit aut",
    "body": "et iusto sed quo iure\nvoluptatem occaecati omnis eligendi aut ad\nvoluptatem doloribus vel accusantium quis pariatur\nmolestiae porro eius odio et labore et velit aut"
  },
  {
    "userId": 1,
    "id": 4,
    "title": "eum et est occaecati",
    "body": "ullam et saepe reiciendis voluptatem adipisci\nsit amet autem assumenda provident rerum culpa\nquis hic commodi nesciunt rem tenetur doloremque ipsam iure\nquis sunt voluptatem rerum illo velit"
  },
  {
    "userId": 1,
    "id": 5,
    "title": "nesciunt quas odio",
    "body": "repudiandae veniam quaerat sunt sed\nalias aut fugiat sit autem sed est\nvoluptatem omnis possimus esse voluptatibus quis\nest aut tenetur dolor neque"
  },
  {
    "userId": 1,
    "id": 6,
    "title": "dolorem eum magni eos aperiam quia",
    "body": "ut aspernatur corporis harum nihil quis provident sequi\nmollitia nobis aliquid molestiae\nperspiciatis et ea nemo ab reprehenderit accusantium quas\nvoluptate dolores velit et doloremque molestiae"
  },
  {
    "userId": 1,
    "id": 7,
    "title": "magnam facilis autem",
    "body": "dolore placeat quibusdam ea quo vitae\nmagni quis enim qui quis quo nemo aut saepe\nquidem repellat excepturi ut quia\nsunt ut sequi eos ea sed quas"
  },
  {
    "userId": 1,
    "id": 8,
    "title": "dolorem dolore est ipsam",
    "body": "dignissimos aperiam dolorem qui eum\nfacilis quibusdam animi sint suscipit qui sint possimus cum\nquaerat magni maiores excepturi\nipsam ut commodi dolor voluptatum modi aut vitae"
  },
  {
    "userId": 1,
    "id": 9,
    "title": "nesciunt iure omnis dolorem tempora et accusantium",
    "body": "consectetur animi nesciunt iure dolore\nenim quia ad\nveniam autem ut quam aut nobis\net est aut quod aut provident voluptas autem voluptas"
  },
  {
    "userId": 1,
    "id": 10,
    "title": "optio molestias id quia eum",
    "body": "quo et expedita modi cum officia vel magni\ndoloribus qui repudiandae\nvero nisi sit\nquos veniam quod sed accusamus veritatis error"
  }
]
```

### Observation

The server returned **200 OK** and a JSON array containing posts associated with user ID 1. The query parameter `userId=1` filters the collection. The `Transfer-Encoding: chunked` header indicates that the response body was transferred in chunks rather than with a declared `Content-Length` header.

**Evidence file:** `evidence/03-list-filtered.txt`

---

## Request 4 — Retrieve Comments for a Post

### Command

```cmd
curl.exe -i -v https://jsonplaceholder.typicode.com/posts/1/comments
```

### Request

```http
GET /posts/1/comments HTTP/1.1
Host: jsonplaceholder.typicode.com
User-Agent: curl/8.21.0
Accept: */*
```

### Response

```http
HTTP/1.1 200 OK
Content-Type: application/json; charset=utf-8
Transfer-Encoding: chunked
Connection: keep-alive
Cache-Control: max-age=43200
```

Response body:

```json
[
  {
    "postId": 1,
    "id": 1,
    "name": "id labore ex et quam laborum",
    "email": "Eliseo@gardner.biz",
    "body": "laudantium enim quasi est quidem magnam voluptate ipsam eos\ntempora quo necessitatibus\ndolor quam autem quasi\nreiciendis et nam sapiente accusantium"
  },
  {
    "postId": 1,
    "id": 2,
    "name": "quo vero reiciendis velit similique earum",
    "email": "Jayne_Kuhic@sydney.com",
    "body": "est natus enim nihil est dolore omnis voluptatem numquam\net omnis occaecati quod ullam at\nvoluptatem error expedita pariatur\nnihil sint nostrum voluptatem reiciendis et"
  },
  {
    "postId": 1,
    "id": 3,
    "name": "odio adipisci rerum aut animi",
    "email": "Nikita@garfield.biz",
    "body": "quia molestiae reprehenderit quasi aspernatur\naut expedita occaecati aliquam eveniet laudantium\nomnis quibusdam delectus saepe quia accusamus maiores nam est\ncum et ducimus et vero voluptates excepturi deleniti ratione"
  },
  {
    "postId": 1,
    "id": 4,
    "name": "alias odio sit",
    "email": "Lew@alysha.tv",
    "body": "non et atque\noccaecati deserunt quas accusantium unde odit nobis qui voluptatem\nquia voluptas consequuntur itaque dolor\net qui rerum deleniti ut occaecati"
  },
  {
    "postId": 1,
    "id": 5,
    "name": "vero eaque aliquid doloribus et culpa",
    "email": "Hayden@biz",
    "body": "harum non quasi et ratione\ntempore iure ex voluptates in ratione\nharum architecto fugit inventore cupiditate\nvoluptates magni quo et"
  }
]
```

### Observation

The server returned **200 OK**, indicating that the comments associated with post ID 1 were retrieved. The endpoint `/posts/1/comments` represents a nested resource: comments belonging to a particular post. The response is JSON encoded using UTF-8.

**Evidence file:** `evidence/04-nested-comments.txt`

---

## Request 5 — Request a Non-Existent Post (Expected Failure)

### Command

```cmd
curl.exe -i -v https://jsonplaceholder.typicode.com/posts/99999
```

### Request

```http
GET /posts/99999 HTTP/1.1
Host: jsonplaceholder.typicode.com
User-Agent: curl/8.21.0
Accept: */*
```

### Response

```http
HTTP/1.1 404 Not Found
Content-Type: application/json; charset=utf-8
Content-Length: 2
```

Response body:

```json
[]
```

### Observation

The server returned **404 Not Found**, demonstrating an unsuccessful lookup for the requested resource. The response has a JSON content type and a content length of two bytes, consistent with an empty JSON array. This request was deliberately made to test how the API responds when a post does not exist.

**Evidence file:** `evidence/05-not-found-404.txt`

---

## Summary of Results

| No. | Request | Status | Result |
|---:|---|---|---|
| 1 | `GET /users/1` | `200 OK` | User record retrieved |
| 2 | `GET /posts/1` | `200 OK` | Single post retrieved |
| 3 | `GET /posts?userId=1` | `200 OK` | Filtered collection retrieved |
| 4 | `GET /posts/1/comments` | `200 OK` | Nested comments retrieved |
| 5 | `GET /posts/99999` | `404 Not Found` | Missing resource tested |

## Conclusion

The five HTTP requests demonstrated how a client can retrieve individual resources, query a filtered collection, access nested resources, and test a missing resource using HTTP GET requests. The response status codes indicated whether each request succeeded, while the `Content-Type` header identified JSON as the response representation. The verbose curl output also exposed the HTTP request lines and response headers exchanged between the client and server.

The original command outputs are retained in the `evidence/` directory for reference.