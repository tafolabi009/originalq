import { Header } from "@/components/layout/header"
import { Footer } from "@/components/layout/footer"

export default function AboutPage() {
  return (
    <div className="min-h-screen flex flex-col">
      <Header />
      <main className="flex-1 py-12 px-6">
        <div className="max-w-7xl mx-auto">
          <h1 className="text-3xl font-bold mb-6">About Us</h1>
          <p className="text-lg text-gray-600 mb-8">
            This page is under construction. Soon you'll be able to learn more about IqraPath.
          </p>
          <div className="bg-teal-50 p-6 rounded-lg border border-teal-100">
            <h2 className="text-xl font-semibold text-teal-700 mb-2">Coming Soon</h2>
            <p className="text-teal-600">
              We're working hard to bring you more information about our mission and team. Check back soon!
            </p>
          </div>
        </div>
      </main>
      <Footer />
    </div>
  )
}

