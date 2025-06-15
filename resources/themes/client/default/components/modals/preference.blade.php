<form action="{{ route('preference.update') }}" method="POST">
  @csrf
  <div class="modal" id="modal_preference">
    <div class="card">
      <div class="header">
        <h2>{{ __('client.modal_preference') }}</h2>
        <button class="btn btn-secondary btn-square" id="modal-close">
          <x-tabler-x/>
        </button>
      </div>
      <div class="body">
        <div class="form-group">
          <label for="language">{{ __('client.language') }}</label>
          <select name="language" id="language">
            @foreach ($langs as $lang => $name)
              <option value="{{ $lang }}" {{ session('locale', config('app.locale')) == $lang ? 'selected' : '' }}>
                {{ $name }}
              </option>
            @endforeach
          </select>
        </div>
      </div>
      <div class="footer">
        <button type="button" class="btn btn-secondary" id="modal-close">{{ __('client.modal_cancel') }}</button>
        <button type="submit" class="btn btn-primary">{{ __('client.modal_save') }}</button>
      </div>
    </div>
  </div>
</form>