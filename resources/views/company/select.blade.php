<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilih Perusahaan - Si-Majter</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center">
        <div class="max-w-md w-full bg-white rounded-lg shadow-md p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Si-Majter</h1>
                <p class="text-gray-600 mt-2">Pilih Perusahaan untuk melanjutkan</p>
            </div>

            <form method="POST" action="{{ route('company.store') }}">
                @csrf
                
                <div class="space-y-4">
                    @foreach($companies as $company)
                        <label class="flex items-center p-4 border rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="radio" name="company_id" value="{{ $company->company_id }}" 
                                   class="mr-3" required>
                            <div>
                                <div class="font-medium text-gray-900">{{ $company->name }}</div>
                                <div class="text-sm text-gray-500">{{ $company->code }}</div>
                            </div>
                        </label>
                    @endforeach
                </div>

                @error('company_id')
                    <div class="mt-2 text-red-600 text-sm">{{ $message }}</div>
                @enderror

                <button type="submit" 
                        class="w-full mt-6 bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 focus:ring-2 focus:ring-blue-500">
                    Lanjutkan
                </button>
            </form>

            <div class="mt-4 text-center">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-gray-500 hover:text-gray-700 text-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>