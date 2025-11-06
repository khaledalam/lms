@props(['title' => '', 'value' => 0])
<div {{ $attributes->merge(['class' => 'bg-white border border-gray-100 shadow-sm rounded-xl p-4']) }}>
    <div class="text-[12px] text-gray-500">{{ $title }}</div>
    <div class="text-2xl font-semibold mt-1 leading-none">{{ $value }}</div>
</div>
