# API (LMS) — Documentation

All endpoints are JSON and live under `/api/*` unless noted.  
- **Base URL (local):** `http://localhost:8000`  
- **Auth:** Bearer token via **Laravel Sanctum** (personal access tokens)  
- **Default Content-Type:** `application/json`

---

## Auth

### 1) Get a token via API
**POST** `/api/auth/token`

**Body (JSON)**
```json
{
  "email": "instructor1@example.com",
  "password": "password"
}
```

**Response (200)**
```json
{ "token": "PASTE_ME" }
```

> **Postman auto‑save:** In the collection, the **Tests** tab for this request saves the returned token to both the **environment** and **collection** variables named `token`. You can reuse it in headers as `Bearer {{token}}`.
```js
// Parse JSON response
const response = pm.response.json();

// Save token to environment and collection variables if present
if (response.token) {
  pm.environment.set("token", response.token);
  pm.collectionVariables.set("token", response.token);
  console.log("Token saved to environment and collection variables");
} else {
  console.warn("⚠️ No token field found in response");
}
```

### 2) Example header
```
Authorization: Bearer PASTE_ME
Accept: application/json
```

> You can also generate a token manually via Tinker if needed:
```bash
php artisan tinker
>>> $u = \App\Models\User::where('email','instructor1@example.com')->first();
>>> $token = $u->createToken('cli')->plainTextToken;  // copy this value
```

---

## Courses

### GET `/api/courses?search=&published=1`
Returns a list of courses. Supports simple search and published filtering.

**Query params**
- `search` (optional): substring match on title/description
- `published` (optional): `1` or `0`

**Example**
```bash
curl -H "Authorization: Bearer $TOKEN"      -H "Accept: application/json"      "http://localhost:8000/api/courses?search=php&published=1"
```

**Response 200 (array)**
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
Creates a course.

**Body**
```json
{
  "title": "API Design 101",
  "description": "Structure, versioning, docs.",
  "published": true
}
```

**Example**
```bash
curl -X POST   -H "Authorization: Bearer $TOKEN"   -H "Accept: application/json"   -H "Content-Type: application/json"   -d '{"title":"API Design 101","description":"Structure, versioning, docs.","published":true}'   http://localhost:8000/api/courses
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
curl -H "Authorization: Bearer $TOKEN"      -H "Accept: application/json"      http://localhost:8000/api/courses/12
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
curl -X PATCH   -H "Authorization: Bearer $TOKEN"   -H "Accept: application/json"   -H "Content-Type: application/json"   -d '{"published":false}'   http://localhost:8000/api/courses/12
```

**Response 200** – updated course JSON.

---

### DELETE `/api/courses/{course}`  *(instructor owner)*
```bash
curl -X DELETE   -H "Authorization: Bearer $TOKEN"   -H "Accept: application/json"   http://localhost:8000/api/courses/12
```
**Response 204** – no content.

---

## Lessons

### GET `/api/courses/{course}/lessons`
List lessons of a course.
```bash
curl -H "Authorization: Bearer $TOKEN"      -H "Accept: application/json"      http://localhost:8000/api/courses/12/lessons
```
**Response 200**
```json
[
  {"id":101,"course_id":12,"order":1,"title":"Intro","content":"..."},
  {"id":102,"course_id":12,"order":2,"title":"HTTP","content":"..."}
]
```

---

### POST `/api/courses/{course}/lessons`
Create a lesson (JSON).
```bash
curl -X POST   -H "Authorization: Bearer $TOKEN"   -H "Accept: application/json"   -H "Content-Type: application/json"   -d '{"title":"Auth","content":"Tokens and cookies"}'   http://localhost:8000/api/courses/12/lessons
```
**Response 201**
```json
{"id":110,"course_id":12,"order":3,"title":"Auth","content":"Tokens and cookies"}
```

---

### GET `/api/lessons/{lesson}`
Fetch a single lesson.
```bash
curl -H "Authorization: Bearer $TOKEN"      -H "Accept: application/json"      http://localhost:8000/api/lessons/110
```
**Response 200**
```json
{"id":110,"course_id":12,"order":3,"title":"Auth","content":"Tokens and cookies"}
```

---

### POST `/api/courses/{course}/lessons` (upload attachment)
Create a lesson **with file** using multipart form-data.
- `title` (text, required)
- `content` (text, optional)
- `attachment` (file, optional)

**Example (curl)**
```bash
curl -X POST   -H "Authorization: Bearer $TOKEN"   -H "Accept: application/json"   -F "title=Lesson 1 - Overview"   -F "content=Lesson intro..."   -F "attachment=@/path/to/file.pdf"   http://localhost:8000/api/courses/11/lessons
```
**Response 201**
```json
{
  "id": 119,
  "course_id": 11,
  "order": 1,
  "title": "Lesson 1 - Overview",
  "content": "Lesson intro...",
  "has_attachment": true,
  "attachment_url": "http://localhost:8000/api/lessons/119/attachment",
  "created_at": "2025-11-06T21:35:50.000000Z"
}
```

> Notes
> - If you upload a file, `has_attachment` should be `true` and `attachment_url` will be available for download.
> - Attachments can later be replaced or removed via `PATCH /api/lessons/{id}` (see below).

---

### PATCH `/api/lessons/{id}` (update + replace/remove attachment)
Supports updating text fields and managing the attachment.
- JSON fields: `title`, `content`
- Multipart fields:
  - `attachment` (file) → **replaces** existing file
  - `remove_attachment=1` (text/boolean) → **removes** existing file

**Examples**
Replace file:
```bash
curl -X PATCH   -H "Authorization: Bearer $TOKEN"   -H "Accept: application/json"   -F "attachment=@/path/to/new.pdf"   http://localhost:8000/api/lessons/119
```

Remove file:
```bash
curl -X PATCH   -H "Authorization: Bearer $TOKEN"   -H "Accept: application/json"   -F "remove_attachment=1"   http://localhost:8000/api/lessons/119
```

**Response 200**
```json
{
  "id": 119,
  "course_id": 11,
  "order": 1,
  "title": "Lesson 1 - Overview",
  "content": "Lesson intro.",
  "has_attachment": false,
  "attachment_url": null,
  "created_at": "2025-11-06T21:35:50.000000Z"
}
```

---

### GET `/api/lessons/{id}/attachment` (download)
Downloads the lesson’s attachment (uses content-disposition filename).
```bash
curl -L   -H "Authorization: Bearer $TOKEN"   http://localhost:8000/api/lessons/11/attachment -o lesson-11-attachment
```

---

## Comments (on lessons)

### GET `/api/lessons/{lesson}/comments`
```bash
curl -H "Authorization: Bearer $TOKEN"      -H "Accept: application/json"      http://localhost:8000/api/lessons/110/comments
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
curl -X POST   -H "Authorization: Bearer $TOKEN"   -H "Accept: application/json"   -H "Content-Type: application/json"   -d '{"body":"Thanks for the lesson!"}'   http://localhost:8000/api/lessons/110/comments
```
**Response 201**
```json
{"id":9,"user_id":3,"lesson_id":110,"body":"Thanks for the lesson!","created_at":"2025-11-05T16:40:00Z"}
```

---

## Enrollment

### POST `/api/courses/{course}/enroll`  *(student)*
Enrolls the authenticated student into a published course (idempotent).
```bash
curl -X POST   -H "Authorization: Bearer $TOKEN"   -H "Accept: application/json"   http://localhost:8000/api/courses/12/enroll
```
**Response 200**
```json
{"status":"ok","message":"Enrolled"}
```

### GET `/api/me/courses`
Returns courses the current user is enrolled in (and taught, if instructor).
```bash
curl -H "Authorization: Bearer $TOKEN"      -H "Accept: application/json"      http://localhost:8000/api/me/courses
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

## Errors

- **401 Unauthorized** — missing or invalid token.  
- **403 Forbidden** — authenticated but not permitted.  
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
- When testing with Postman, prefer the collection/environment `token` variable and set `Authorization: Bearer {{token}}` on requests.