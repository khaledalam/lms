<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class CourseApiController extends Controller
{
    /**
     * GET /api/courses
     * - Query params:
     *   - search: string (title/description LIKE)
     *   - published: 0|1 (filter published)
     *   - mine: 0|1 (instructor: only own courses)
     *   - per_page: int (pagination, default 15, max 100)
     *
     * Students: see only published courses (unless policy/view allows more).
     * Instructors: can pass ?mine=1 to see their own quickly.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $q = Course::query();

        // search
        if ($s = trim((string) $request->query('search', ''))) {
            $q->where(function ($qq) use ($s) {
                $like = '%' . str_replace(['%', '_'], ['\%', '\_'], $s) . '%';
                $qq->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        // published filter (explicit)
        if (!is_null($request->query('published'))) {
            $q->where('published', (bool) $request->boolean('published'));
        } else {
            // default: students see only published
            if (!$user->isInstructor()) {
                $q->where('published', true);
            }
        }

        // mine=1 (instructor)
        if ($request->boolean('mine') && $user->isInstructor()) {
            $q->where('instructor_id', $user->id);
        }

        $perPage = min(max((int) $request->query('per_page', 15), 1), 100);

        return response()->json(
            $q->orderByDesc('id')
                ->select(['id', 'title', 'slug', 'description', 'published', 'instructor_id'])
                ->paginate($perPage)
        );
    }

    /**
     * POST /api/courses  (instructor only)
     */
    public function store(Request $request)
    {
        $this->authorize('create', Course::class);

        $validated = $request->validate([
            'title'       => 'required|string|max:150',
            'description' => 'nullable|string',
            'published'   => 'sometimes|boolean',
        ]);

        $slug = $this->uniqueSlug($validated['title']);

        $course = Course::create([
            'instructor_id' => Auth::id(),
            'title'         => $validated['title'],
            'slug'          => $slug,
            'description'   => $validated['description'] ?? null,
            'published'     => (bool) ($validated['published'] ?? false),
        ]);

        return response()->json($course->only(['id', 'title', 'slug', 'description', 'published', 'instructor_id']), 201);
    }

    /**
     * GET /api/courses/{course}
     */
    public function show(Course $course)
    {
        $this->authorize('view', $course);

        return response()->json(
            $course->only(['id', 'title', 'slug', 'description', 'published', 'instructor_id'])
        );
    }

    /**
     * PATCH /api/courses/{course}  (instructor owner)
     */
    public function update(Request $request, Course $course)
    {
        $this->authorize('update', $course);

        $validated = $request->validate([
            'title'       => 'sometimes|required|string|max:150',
            'description' => 'sometimes|nullable|string',
            'published'   => 'sometimes|boolean',
        ]);

        if (array_key_exists('title', $validated) && $validated['title'] !== $course->title) {
            $course->title = $validated['title'];
            $course->slug  = $this->uniqueSlug($validated['title'], $course->id);
        }

        if (array_key_exists('description', $validated)) {
            $course->description = $validated['description'];
        }

        if (array_key_exists('published', $validated)) {
            $course->published = (bool) $validated['published'];
        }

        $course->save();

        return response()->json(
            $course->only(['id', 'title', 'slug', 'description', 'published', 'instructor_id'])
        );
    }

    /**
     * DELETE /api/courses/{course}  (instructor owner)
     */
    public function destroy(Course $course)
    {
        $this->authorize('delete', $course);
        $course->delete();
        return response()->noContent();
    }

    /**
     * Generate a unique slug for title.
     */
    private function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        $i = 1;

        while (
            Course::where('slug', $slug)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = $base . '-' . $i;
            $i++;
        }

        return $slug;
    }
}
