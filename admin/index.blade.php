{{-- This assumes you have a layout file, e.g., app.blade.php --}}
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Manage Notifications') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">

                    {{-- Success Message --}}
                    @if (session('success'))
                        <div class="mb-4 p-4 bg-green-100 text-green-700 border border-green-200 rounded">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Add Notification Form --}}
                    <form method="POST" action="{{ route('admin.notifications.store') }}" class="mb-6">
                        @csrf
                        <div>
                            <label for="text" class="block font-medium text-sm text-gray-700">{{ __('New Notification Text') }}</label>
                            <textarea id="text" name="text" rows="3" class="block mt-1 w-full rounded-md shadow-sm border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" required autofocus>{{ old('text') }}</textarea>
                            @error('text')
                                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 active:bg-gray-900 focus:outline-none focus:border-gray-900 focus:ring ring-gray-300 disabled:opacity-25 transition ease-in-out duration-150">
                                {{ __('Add Notification') }}
                            </button>
                        </div>
                    </form>

                    {{-- Existing Notifications --}}
                    <h3 class="text-lg font-semibold mb-4">Existing Notifications</h3>
                    <div class="space-y-4">
                        @forelse ($notifications as $notification)
                            <div class="p-4 border rounded-lg flex justify-between items-center">
                                <div>
                                    <p class="text-gray-800">{{ $notification->text }}</p>
                                    <p class="text-sm text-gray-500">{{ $notification->created_at->diffForHumans() }}</p>
                                </div>
                                <form method="POST" action="{{ route('admin.notifications.destroy', $notification) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Are you sure you want to delete this notification?')">Delete</button>
                                </form>
                            </div>
                        @empty
                            <p>No notifications yet.</p>
                        @endforelse
                    </div>

                    <div class="mt-6">
                        {{ $notifications->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>