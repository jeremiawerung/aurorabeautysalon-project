<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Password Admin</title>
</head>
<body>
    <h1>Perbarui Password Admin</h1>
    <form action="{{ route('admin.updatePassword', $admin->id) }}" method="POST">
        @csrf
        @method('PUT')  <!-- Menggunakan method PUT untuk update data -->

        <label for="password">Password Baru:</label>
        <input type="password" name="password" id="password" required>

        <label for="password_confirmation">Konfirmasi Password Baru:</label>
        <input type="password" name="password_confirmation" id="password_confirmation" required>

        <button type="submit">Update Password</button>
    </form>



    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif
</body>
</html>
