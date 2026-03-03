<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Realtime Chat</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950 text-slate-100 flex items-center justify-center p-6">
<div class="w-full max-w-md rounded-2xl border border-slate-800/80 bg-slate-900/80 p-6 shadow-2xl backdrop-blur animate-[pulse_4s_ease-in-out_infinite]">
    <h1 class="text-2xl font-semibold mb-2">Welcome back</h1>
    <p class="text-sm text-slate-400 mb-6">Sign in to continue chatting in realtime.</p>
    @if ($errors->any())
        <div class="mb-4 rounded-lg border border-rose-500/30 bg-rose-500/10 p-3 text-sm text-rose-300">{{ $errors->first() }}</div>
    @endif
    <form method="post" action="{{ route('login.store') }}" class="space-y-4">
        @csrf
        <label class="block text-sm">Username
            <input name="username" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 focus:border-indigo-400 outline-none" value="{{ old('username') }}">
        </label>
        <label class="block text-sm">Password
            <input type="password" name="password" required class="mt-1 w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2 focus:border-indigo-400 outline-none">
        </label>
        <label class="inline-flex items-center gap-2 text-xs text-slate-400">
            <input type="checkbox" name="remember" class="rounded border-slate-600 bg-slate-800"> Remember me
        </label>
        <button class="w-full rounded-lg bg-indigo-500 hover:bg-indigo-400 transition-colors px-4 py-2 font-medium">Login</button>
    </form>
    <p class="mt-4 text-sm text-slate-400">No account? <a class="text-indigo-300 hover:text-indigo-200" href="{{ route('register') }}">Create one</a></p>
</div>
</body>
</html>
