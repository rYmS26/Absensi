<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Leave Request Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                            <span class="block sm:inline">{{ session('success') }}</span>
                        </div>
                    @endif

                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Leave Request Information</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Employee</p>
                            <div class="mt-1 flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    @if ($leaveRequest->user->profile_photo)
                                        <img class="h-10 w-10 rounded-full object-cover" src="{{ Storage::url($leaveRequest->user->profile_photo) }}" alt="{{ $leaveRequest->user->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center">
                                            <span class="text-gray-500 font-medium">{{ substr($leaveRequest->user->name, 0, 1) }}</span>
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $leaveRequest->user->name }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $leaveRequest->user->department }} - {{ $leaveRequest->user->position }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Status</p>
                            <p class="mt-1 text-sm">
                                @if ($leaveRequest->status === 'pending')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                @elseif ($leaveRequest->status === 'approved')
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                        Approved
                                    </span>
                                @else
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                        Rejected
                                    </span>
                                @endif
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Leave Type</p>
                            <p class="mt-1 text-sm text-gray-900">{{ ucfirst($leaveRequest->type) }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Duration</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1 }} day(s)</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Start Date</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->start_date->format('F j, Y') }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">End Date</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->end_date->format('F j, Y') }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Submitted On</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->created_at->format('F j, Y') }}</p>
                        </div>

                        <div class="md:col-span-2">
                            <p class="text-sm font-medium text-gray-500">Reason</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->reason }}</p>
                        </div>

                        @if ($leaveRequest->admin_remarks)
                            <div class="md:col-span-2">
                                <p class="text-sm font-medium text-gray-500">Admin Remarks</p>
                                <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->admin_remarks }}</p>
                            </div>
                        @endif
                    </div>

                    @if ($leaveRequest->status === 'pending')
                        <div class="border-t border-gray-200 pt-6">
                            <h4 class="text-md font-medium text-gray-900 mb-4">Update Leave Request Status</h4>

                            <form action="{{ route('admin.leave.update-status', $leaveRequest) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 gap-6">
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                                        <select id="status" name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                            <option value="approved">Approve</option>
                                            <option value="rejected">Reject</option>
                                        </select>
                                    </div>

                                    <div>
                                        <label for="admin_remarks" class="block text-sm font-medium text-gray-700 mb-1">Remarks</label>
                                        <textarea id="admin_remarks" name="admin_remarks" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    </div>
                                </div>

                                <div class="mt-6 flex justify-end">
                                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                        Update Status
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                    <div class="mt-6">
                        <a href="{{ route('admin.leave.index') }}" class="text-blue-600 hover:text-blue-800">
                            ← Back to Leave Requests
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
