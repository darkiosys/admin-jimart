<div class="form-group">
    <input class="form-control" name="user_id" type="hidden" id="user_id" value="{{ Auth::user()->id }}" >
</div>
<div class="form-group {{ $errors->has('saldo') ? 'has-error' : ''}}">
    <label for="saldo" class="control-label">{{ 'Masukan Username yang di tuju' }}</label>
    <input class="form-control" name="username" type="text" id="saldo" value="{{ isset($saldo->saldo) ? $saldo->saldo : ''}}" >
    {!! $errors->first('saldo', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group {{ $errors->has('jumlah_transfer') ? 'has-error' : ''}}">
    <label for="jumlah_transfer" class="control-label">{{ 'Jumlah Transfer' }}</label>
    <input class="form-control" name="jumlah_transfer" type="number" id="jumlah_transfer" value="{{ isset($saldo->jumlah_transfer) ? $saldo->jumlah_transfer : ''}}" >
    {!! $errors->first('jumlah_transfer', '<p class="help-block">:message</p>') !!}
</div>
<div class="form-group {{ $errors->has('no_rek') ? 'has-error' : ''}}">
    <label for="no_rek" class="control-label">{{ 'Password' }}</label>
    <input class="form-control" name="password" type="password" id="no_rek" value="{{ isset($saldo->no_rek) ? $saldo->no_rek : ''}}" >
    {!! $errors->first('no_rek', '<p class="help-block">:message</p>') !!}
</div>

<div class="form-group">
    <input class="btn btn-primary" type="submit" value="{{ $formMode === 'edit' ? 'Update' : 'Transfer Saldo' }}">
</div>
