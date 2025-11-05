# API (LMS) — Documentation

All endpoints are JSON and live under `/api/*`.

- **Base URL (local):** `http://localhost:8000`
- **Auth:** Bearer token via **Laravel Sanctum** (personal access tokens)
- **Content-Type:** `application/json`

> If you haven’t finished Sanctum setup, you can still test web routes with session auth. For API testing, generate a **personal access token** as shown below.

---

## Auth (Sanctum token example)

### Create a token (via Tinker)
```bash
php artisan tinker
>>> $u = \App\Models\User::where('email','instructor1@example.com')->first();
>>> $token = $u->createToken('cli')->plainTextToken;
>>> $token
"PASTE_ME"
```

Use that value in the `Authorization: Bearer` header.

### Example header
```
Authorization: Bearer PASTE_ME
Accept: application/json
```

---

## Courses

### GET `/api/courses?search=&published=1`
Returns a list of courses. Supports simple search and published filtering.

**Query params**
- `search` (optional): substring match on title/description
- `published` (optional): `1` or `0`

**Request**
```bash
curl -s   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   "http://localhost:8000/api/courses?search=php&published=1"
```

**Response 200**
```json
[
  {
    "id": 5,
    "title": "Intro to PHP",
    "slug": "intro-to-php",
    "description": "Basics",
    "published": true,
    "instructor_id": 2
  }
]
```

---

### POST `/api/courses`  *(instructor only)*
Creates a course. Only users with role `instructor` are authorized.

**Body**
```json
{
  "title": "API Design 101",
  "description": "Structure, versioning, docs.",
  "published": true
}
```

**Request**
```bash
curl -s -X POST   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   -H "Content-Type: application/json"   -d '{"title":"API Design 101","description":"Structure, versioning, docs.","published":true}'   http://localhost:8000/api/courses
```

**Response 201**
```json
{
  "id": 12,
  "title": "API Design 101",
  "slug": "api-design-101",
  "description": "Structure, versioning, docs.",
  "published": true,
  "instructor_id": 2
}
```

---

### GET `/api/courses/{course}`
Fetch a single course.

```bash
curl -s   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   http://localhost:8000/api/courses/12
```

**Response 200**
```json
{
  "id": 12,
  "title": "API Design 101",
  "slug": "api-design-101",
  "description": "Structure, versioning, docs.",
  "published": true,
  "instructor_id": 2
}
```

---

### PATCH `/api/courses/{course}`  *(instructor owner)*
```bash
curl -s -X PATCH   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   -H "Content-Type: application/json"   -d '{"published":false}'   http://localhost:8000/api/courses/12
```

**Response 200** – updated course JSON.

---

### DELETE `/api/courses/{course}`  *(instructor owner)*
```bash
curl -s -X DELETE   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   http://localhost:8000/api/courses/12
```

**Response 204** – no content.

---

## Nested: Lessons

### GET `/api/courses/{course}/lessons`
```bash
curl -s   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   http://localhost:8000/api/courses/12/lessons
```

**Response 200**
```json
[
  {"id":101,"course_id":12,"order":1,"title":"Intro","content":"..."},
  {"id":102,"course_id":12,"order":2,"title":"HTTP","content":"..."}
]
```

---

### POST `/api/courses/{course}/lessons`  *(instructor owner)*
```bash
curl -s -X POST   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   -H "Content-Type: application/json"   -d '{"title":"Auth","content":"Tokens and cookies"}'   http://localhost:8000/api/courses/12/lessons
```

**Response 201**
```json
{"id":110,"course_id":12,"order":3,"title":"Auth","content":"Tokens and cookies"}
```

---

### GET `/api/lessons/{lesson}`
```bash
curl -s   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   http://localhost:8000/api/lessons/110
```

**Response 200**
```json
{"id":110,"course_id":12,"order":3,"title":"Auth","content":"Tokens and cookies"}
```

---

## Enrollment

### POST `/api/courses/{course}/enroll`  *(student)*
Enrolls the authenticated student into a published course (idempotent).

```bash
curl -s -X POST   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   http://localhost:8000/api/courses/12/enroll
```

**Response 200**
```json
{"status":"ok","message":"Enrolled"}
```

### GET `/api/me/courses`
Returns courses the current user is enrolled in (and taught, if instructor).

```bash
curl -s   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   http://localhost:8000/api/me/courses
```

**Response 200**
```json
{
  "enrolled": [
    {"id":12,"title":"API Design 101","slug":"api-design-101","published":true}
  ],
  "instructed": [
    {"id":5,"title":"Intro to PHP","slug":"intro-to-php","published":true}
  ]
}
```

---

## Comments (on lessons)

### GET `/api/lessons/{lesson}/comments`
```bash
curl -s   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   http://localhost:8000/api/lessons/110/comments
```

**Response 200**
```json
[
  {"id":1,"user_id":3,"lesson_id":110,"body":"Great!","created_at":"2025-11-05T16:23:10Z"}
]
```

### POST `/api/lessons/{lesson}/comments`
Allowed for **instructor of the course** or **enrolled students**.

```bash
curl -s -X POST   -H "Authorization: Bearer PASTE_ME"   -H "Accept: application/json"   -H "Content-Type: application/json"   -d '{"body":"Thanks for the lesson!"}'   http://localhost:8000/api/lessons/110/comments
```

**Response 201**
```json
{"id":9,"user_id":3,"lesson_id":110,"body":"Thanks for the lesson!","created_at":"2025-11-05T16:40:00Z"}
```

---

## Errors

- **401 Unauthorized** — missing or invalid token.
- **403 Forbidden** — authenticated but not permitted (e.g., student trying to create a course).
- **404 Not Found** — resource doesn’t exist or you don’t have access.
- **422 Unprocessable Entity** — validation failed (check `errors` in JSON).

**Example 422**
```json
{
  "message": "The given data was invalid.",
  "errors": { "title": ["The title field is required."] }
}
```

---

## Notes

- All write endpoints require auth; course creation/updating/deleting is **instructor-only** (policy).
- Slugs are generated automatically on create; you may pass one explicitly if needed.
- Lesson order is maintained per course; UI offers up/down controls on the web side.

---

Commit message:
```
docs: add API documentation
```