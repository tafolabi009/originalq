"use client"
import Link from "next/link"
import { AlertTriangle } from "lucide-react"

export function RegistrationSuccess({ name }) {
  return (
    <div className="text-center space-y-6">
      <div className="flex justify-center">
        <div className="bg-teal-100 rounded-full p-6">
          <svg className="h-16 w-16 text-teal-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round"  fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 13l4 4L19 7" />
          </svg>
        </div>
      </div>

      <h2 className="text-2xl font-bold">Thank you for completing registration!</h2>
      <p className="text-gray-600">We've received your application and are currently reviewing it.</p>

      <div className="bg-white p-6 rounded-lg shadow-sm border border-gray-100 max-w-md mx-auto">
        <p className="text-gray-800 mb-4">
          To ensure the quality and authenticity of our teachers, we require a quick live video call before you can
          proceed to your dashboard.
        </p>
        <p className="text-gray-800 mb-4">
          You will receive an email with the invitation live video call within 5 business days. Stay tuned!
        </p>

        <div className="bg-amber-50 border-l-4 border-amber-500 p-4 text-left">
          <h3 className="font-bold text-amber-800 mb-2">Important Notes</h3>
          <ul className="space-y-2">
            <li className="flex items-start">
              <AlertTriangle className="h-5 w-5 text-amber-500 mr-2 flex-shrink-0 mt-0.5" />
              <span>Make sure to have a stable internet connection.</span>
            </li>
            <li className="flex items-start">
              <AlertTriangle className="h-5 w-5 text-amber-500 mr-2 flex-shrink-0 mt-0.5" />
              <span>Use a quiet and well-lit environment.</span>
            </li>
            <li className="flex items-start">
              <AlertTriangle className="h-5 w-5 text-amber-500 mr-2 flex-shrink-0 mt-0.5" />
              <span>Keep your ID and teaching qualifications ready.</span>
            </li>
          </ul>
        </div>
      </div>

      <div>
        <Link
          href="/"
          className="inline-flex items-center justify-center px-5 py-3 border border-transparent text-base font-medium rounded-md text-white bg-teal-600 hover:bg-teal-700"
        >
          Return to Home
        </Link>
      </div>
    </div>
  );
}

