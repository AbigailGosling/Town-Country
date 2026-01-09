<div>
    @if($type === 'success')
        <a href="{{route($route, $id).$extras}}">
            <button class="rounded bg-green-500 hover:bg-green-700 w-6 h-6" href=""><i class="fas fa-edit text-green-100"></i></button>
        </a>
    @elseif($type === 'clone')
        <a href="{{route($route, $id).$extras}}">
            <button class="rounded bg-yellow-500 hover:bg-yellow-700 w-6 h-6" href=""><i class="fas fa-regular fa-copy text-yellow-100"></i></button>
        </a>
    @elseif($type === 'delete')
        <a href="{{route($route, $id).$extras}}">
            <button class="rounded bg-red-500 hover:bg-red-700 w-6 h-6" href=""><i class="fas fa-trash text-red-100"></i></button>
        </a>
    @endif
</div>
