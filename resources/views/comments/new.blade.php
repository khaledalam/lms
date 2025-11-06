@component('mail::message')
# New Comment Added

{{ $comment->user->name }} commented on **{{ $comment->lesson->title }}**  
in your course **{{ $comment->lesson->course->title }}**.

> "{{ $comment->body }}"

@component('mail::button', ['url' => route('lessons.show', $comment->lesson)])
View Lesson
@endcomponent

Thanks,  
{{ config('app.name') }}
@endcomponent