<div>
  @if($isEnabledlanguage)
  @php
 $allLanguages = [
    'ar' => 'Arabic',
    'zh_CN' => 'Chinese',
    'da' => 'Danish',
    'fr' => 'French',
    'hi' => 'Hindi',
    'ja' => 'Japanese',
    'ms' => 'Malay',
    'pt_BR' => 'Português Brasileiro',
    'ru' => 'Russian',
    'sk' => 'Slovak',
    'es' => 'Spanish',
    'ta' => 'Tamil',
    'th' => 'Thai',
    'tr' => 'Turkish',
    'ur' => 'Urdu',
    ];
  @endphp
  <div class="md:absolute md:right-4 md:top-2">
    <select id="selectLanguage" wire:model.live="chooseLanguage"
      class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-900 shadow-sm focus:border-blue-500 focus:ring-2 focus:ring-blue-500/40 dark:border-gray-600 dark:bg-gray-800 dark:text-white">
      <option value="" disabled>{{ __('Select Language') }}</option>
      <option value="en" {{ $defaultSelected == 'en' ? 'selected' : ""}}>{{ __('English') }}</option>
      @if(!empty($languages))
      @foreach ($languages as $item)
      <option value="{{$item}}" {{ $defaultSelected == $item ? 'selected' : ""}}>{{ __($allLanguages[$item]) }}</option>
      @endforeach
      @endif
    </select>
  </div>
  @endif
</div>