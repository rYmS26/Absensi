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
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Leave Request Information</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Type</p>
                            <p class="mt-1 text-sm text-gray-900">{{ ucfirst($leaveRequest->type) }}</p>
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
                            <p class="text-sm font-medium text-gray-500">Start Date</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->start_date->format('F j, Y') }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">End Date</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->end_date->format('F j, Y') }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-gray-500">Duration</p>
                            <p class="mt-1 text-sm text-gray-900">{{ $leaveRequest->start_date->diffInDays($leaveRequest->end_date) + 1 }} day(s)</p>
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

                    <div class="mt-6">
                        <a href="{{ route('leave.index') }}" class="text-blue-600 hover:text-blue-800">
                            ← Back to Leave Requests
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
