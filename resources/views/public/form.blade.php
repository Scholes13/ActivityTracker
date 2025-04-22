@extends('layouts.public')

@section('title', 'Submit Activities')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-md p-8">
    <h2 class="text-2xl font-semibold text-gray-800 mb-6">Submit Your Activities</h2>
    
    @if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
        <p class="font-bold">Please fix the following errors:</p>
        <ul class="list-disc ml-5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    
    <form action="{{ route('public.form.store') }}" method="POST" x-data="formData()">
        @csrf
        
        <div class="space-y-6">
            <!-- Personal Information -->
            <div class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Your Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700 mb-1">Full Name *</label>
                        <input type="text" name="full_name" id="full_name" class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-md" required value="{{ old('full_name') }}">
                    </div>
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address *</label>
                        <input type="email" name="email" id="email" class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-md" required value="{{ old('email') }}">
                    </div>
                </div>
            </div>
            
            <!-- Form Selection -->
            <div class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Task Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="sub_task_id" class="block text-sm font-medium text-gray-700 mb-1">Select Sub-Task *</label>
                        <select name="sub_task_id" id="sub_task_id" class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-md" required>
                            <option value="">-- Select a Sub-Task --</option>
                            @foreach($subtasks as $subtask)
                                <option value="{{ $subtask->id }}" {{ old('sub_task_id') == $subtask->id ? 'selected' : '' }}>
                                    [{{ $subtask->type }}] {{ $subtask->name }} ({{ $subtask->leader->name }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="date" class="block text-sm font-medium text-gray-700 mb-1">Date *</label>
                        <input type="date" name="date" id="date" class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-md" required value="{{ old('date', date('Y-m-d')) }}">
                    </div>
                </div>
            </div>
            
            <!-- Activities (Dynamic) -->
            <div class="bg-gray-50 p-4 rounded-md">
                <h3 class="text-lg font-medium text-gray-700 mb-4">Activities</h3>
                <p class="text-sm text-gray-500 mb-4">Please add one or more activities that you worked on.</p>
                
                <template x-for="(activity, index) in activities" :key="index">
                    <div class="border border-gray-200 rounded-md p-4 mb-4 bg-white">
                        <div class="flex justify-between items-center mb-3">
                            <h4 class="text-md font-medium text-gray-700">Activity #<span x-text="index + 1"></span></h4>
                            <button type="button" 
                                x-show="index > 0"
                                @click="removeActivity(index)" 
                                class="text-red-600 hover:text-red-800">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div class="mb-3">
                            <label :for="'activities['+index+'][title]'" class="block text-sm font-medium text-gray-700 mb-1">Activity Title *</label>
                            <input type="text" :name="'activities['+index+'][title]'" :id="'activities['+index+'][title]'" 
                                class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-md" 
                                required 
                                x-model="activity.title">
                        </div>
                        
                        <div>
                            <label :for="'activities['+index+'][description]'" class="block text-sm font-medium text-gray-700 mb-1">Description (Optional)</label>
                            <textarea :name="'activities['+index+'][description]'" :id="'activities['+index+'][description]'" 
                                class="border-gray-300 focus:ring-blue-500 focus:border-blue-500 block w-full rounded-md" 
                                rows="3" 
                                x-model="activity.description"></textarea>
                        </div>
                    </div>
                </template>
                
                <button type="button" @click="addActivity" class="mt-2 flex items-center text-blue-600 hover:text-blue-800">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Add More Activity
                </button>
            </div>
            
            <div class="flex justify-end">
                <button type="submit" class="px-6 py-3 bg-blue-600 text-white font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                    Submit Activities
                </button>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function formData() {
        return {
            activities: [
                {
                    title: '',
                    description: ''
                }
            ],
            addActivity() {
                this.activities.push({
                    title: '',
                    description: ''
                });
            },
            removeActivity(index) {
                if (this.activities.length > 1) {
                    this.activities.splice(index, 1);
                }
            }
        }
    }
</script>
@endsection 