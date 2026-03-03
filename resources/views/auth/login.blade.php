<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Realtime Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 flex items-center justify-center p-6">
<div class="w-full max-w-md rounded-2xl border border-slate-800 bg-slate-900 p-6 shadow-2xl">
    <h1 class="text-2xl font-semibold mb-6">Sign in</h1>
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-rose-500/30 bg-rose-500/10 p-3 text-sm text-rose-300">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <label class="block text-sm">Username
            <input name="username" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2" value="{{ old('username') }}">
        </label>
        <label class="block text-sm">Password
            <input type="password" name="password" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2">
        </label>
        <button class="w-full rounded-lg bg-indigo-500 hover:bg-indigo-400 px-4 py-2 font-medium">Login</button>
    </form>
    <p class="mt-4 text-sm text-slate-400">No account? <a class="text-indigo-400" href="{{ route('register') }}">Create one</a></p>
</div>
</body>
</html>
