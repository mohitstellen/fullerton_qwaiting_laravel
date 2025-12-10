<div class="px-4 py-6 sm:px-0">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
            Welcome, {{ $member->salutation ?? '' }} {{ $member->full_name }}
        </h1>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
            <!-- Book an Appointment Card -->
            <a href="#" class="block p-6 bg-blue-50 dark:bg-blue-900/20 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/30 transition">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Book an Appointment</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Schedule your next visit</p>
                    </div>
                </div>
            </a>

            <!-- My Appointments Card -->
            <a href="#" class="block p-6 bg-green-50 dark:bg-green-900/20 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/30 transition">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">My Appointments</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">View your appointments</p>
                    </div>
                </div>
            </a>

            <!-- Profile Card -->
            <a href="#" class="block p-6 bg-purple-50 dark:bg-purple-900/20 rounded-lg hover:bg-purple-100 dark:hover:bg-purple-900/30 transition">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <svg class="w-12 h-12 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Profile</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400">Manage your profile</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Customer Type Info -->
        <div class="mt-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                <strong>Customer Type:</strong> 
                <span class="text-blue-600 dark:text-blue-400">{{ $member->customer_type ?? 'Not Set' }}</span>
            </p>
        </div>
    </div>
</div>
