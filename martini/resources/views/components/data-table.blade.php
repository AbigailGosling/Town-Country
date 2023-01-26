<div class="bg-gray-200 overflow-hidden shadow-sm sm:rounded-lg ml-6 mr-6">
    <table class="border-collapse table-auto w-full text-sm mt-4">
        <thead class="bg-gray-200">
        <tr>
            {{$headers}}
        </tr>
        </thead>
        <tbody class="bg-white">
            {{$slot}}
        </tbody>
    </table>
    @if($agent->isMobile())
        <div class="h-max w-max bg-gray-800">FGHJFGHFHHFD</div>
    @endif
</div>
