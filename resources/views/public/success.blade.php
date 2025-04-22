@extends('layouts.public')

@section('title', 'Submission Successful')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-8 text-center">
    <div class="flex justify-center mb-6">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-green-500" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
    </div>
    
    <h2 class="text-2xl font-semibold text-gray-800 mb-4">Thank You!</h2>
    <p class="text-gray-600 mb-6">Your activities have been successfully submitted. The team will review your submission shortly.</p>
    
    <div class="mt-8">
        <a href="{{ route('public.form') }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-medium rounded-md shadow hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M9.707 14.707a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 1.414L7.414 9H15a1 1 0 110 2H7.414l2.293 2.293a1 1 0 010 1.414z" clip-rule="evenodd" />
            </svg>
            Submit Another Activity
        </a>
    </div>
</div>
@endsection 