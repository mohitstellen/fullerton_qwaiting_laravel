<x-layouts.custom-layout>
    <div class="min-h-screen bg-gray-100 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-5xl mx-auto">
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-white">
                    <h2 class="text-2xl font-bold text-gray-800">Import Data From Excel</h2>
                </div>

                <div class="p-6 md:p-8">
                    @if(session('success'))
                    <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif
                    @if(session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Import Categories -->
                        <div class="bg-gray-50 border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
                            <div class="px-5 py-4 border-b border-gray-200 bg-white rounded-t-xl">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                    </svg>
                                    Import Categories (Level 1)
                                </h3>
                            </div>
                            <div class="p-5">
                                <p class="text-sm text-gray-600 mb-5 leading-relaxed">
                                    Upload an Excel file to import or update categories. <br>
                                    <span class="inline-block mt-1 text-xs font-semibold px-2 py-1 bg-indigo-100 text-indigo-700 rounded-md">Required column: name</span>
                                </p>
                                <form action="{{ route('tenant.import.categories') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-5">
                                        <label for="category_file" class="block text-sm font-medium text-gray-700 mb-2">Excel File</label>
                                        <input type="file" class="block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2.5 file:px-4
                                            file:rounded-full file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-indigo-50 file:text-indigo-700
                                            hover:file:bg-indigo-100
                                            border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none"
                                            name="category_file" id="category_file" required>
                                    </div>
                                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-colors duration-200">
                                        Import Categories
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Import Locations -->
                        <div class="bg-gray-50 border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-shadow duration-300">
                            <div class="px-5 py-4 border-b border-gray-200 bg-white rounded-t-xl">
                                <h3 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-pink-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Import Locations
                                </h3>
                            </div>
                            <div class="p-5">
                                <p class="text-sm text-gray-600 mb-5 leading-relaxed">
                                    Upload an Excel file to import or update locations. <br>
                                    <span class="inline-block mt-1 text-xs font-semibold px-2 py-1 bg-pink-100 text-pink-700 rounded-md">Required column: location_name</span>
                                </p>
                                <form action="{{ route('tenant.import.locations') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="mb-5">
                                        <label for="location_file" class="block text-sm font-medium text-gray-700 mb-2">Excel File</label>
                                        <input type="file" class="block w-full text-sm text-gray-500
                                            file:mr-4 file:py-2.5 file:px-4
                                            file:rounded-full file:border-0
                                            file:text-sm file:font-semibold
                                            file:bg-pink-50 file:text-pink-700
                                            hover:file:bg-pink-100
                                            border border-gray-300 rounded-lg cursor-pointer bg-white focus:outline-none"
                                            name="location_file" id="location_file" required>
                                    </div>
                                    <button type="submit" class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-pink-600 hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 transition-colors duration-200">
                                        Import Locations
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.custom-layout>