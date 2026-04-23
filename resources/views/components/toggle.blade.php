@props(['checked' => false, 'disabled' => false])

<label class="relative inline-flex items-center {{ $disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}">
    <input
        type="checkbox"
        class="sr-only peer"
        {{ $checked ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes }}
    />
    <div class="w-11 h-6 bg-white/10 rounded-full peer peer-checked:bg-orange-400 peer-checked:after:translate-x-5 after:content-[''] after:absolute after:top-1 after:left-1 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-all"></div>
</label>
