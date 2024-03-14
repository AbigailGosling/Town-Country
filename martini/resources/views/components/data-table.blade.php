<div class="bg-gray-200 shadow-sm sm:rounded-lg ml-6 mr-6">
    <table class="border-collapse table-auto w-full text-sm mt-4">
        @if(isset($headers))
        <thead style="position: sticky; top: 0;">
        <tr>
            {{$headers}}
        </tr>
        </thead>
        @endif
        <tbody class="bg-white">
            {{$slot}}
        </tbody>
        @if(isset($footers))
        <tfoot style="position: sticky; bottom: 0;">
        <tr>
            {{$footers}}
        </tr>
        </tfoot>
        @endif
    </table>
</div>
