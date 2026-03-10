@extends('layouts.main') @section('content')
<div class="container mx-auto p-4">
    <h2 class="text-2xl font-bold mb-4">Editar Layout do E-mail para Gestores</h2>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white p-6 rounded shadow">
        <form action="{{ route('admin.email_template.update') }}" method="POST">
            @csrf
            
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Assunto do E-mail</label>
                <input type="text" name="subject" value="{{ old('subject', $template->subject) }}" class="w-full border rounded p-2" required>
            </div>

            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2">Corpo do E-mail (Layout)</label>
                <textarea name="body" rows="10" class="w-full border rounded p-2" required>{{ old('body', $template->body) }}</textarea>
            </div>

            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">Salvar Layout</button>
        </form>
    </div>
</div>
@endsection