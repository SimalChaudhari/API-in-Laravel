<form action="{{ route('invite') }}" method="post">
    {{ csrf_field() }}
    <input type="email" name="email" />
    <button type="submit">Send invite</button>
</form>