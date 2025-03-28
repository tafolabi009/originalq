import Link from "next/link"
import Image from "next/image"
import { Facebook, Instagram, Linkedin, Twitter, Youtube } from "lucide-react"

export function Footer() {
  return (
    <footer className="bg-[#0D6A61] text-white py-12 px-6 mt-auto">
      <div className="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-4 gap-8">
        <div>
          <Image src="/logo-white.svg" alt="IqraPath Logo" width={150} height={40} className="h-10 w-auto mb-4" />
          <p className="text-teal-100 mb-6">Lorem ipsum dolor sit amet consectetur adipiscing elit aliquam</p>
          <div className="flex gap-4">
            <a href="#" className="text-white hover:text-teal-200 transition-colors" aria-label="Facebook">
              <Facebook size={20} />
            </a>
            <a href="#" className="text-white hover:text-teal-200 transition-colors" aria-label="Twitter">
              <Twitter size={20} />
            </a>
            <a href="#" className="text-white hover:text-teal-200 transition-colors" aria-label="Instagram">
              <Instagram size={20} />
            </a>
            <a href="#" className="text-white hover:text-teal-200 transition-colors" aria-label="LinkedIn">
              <Linkedin size={20} />
            </a>
            <a href="#" className="text-white hover:text-teal-200 transition-colors" aria-label="YouTube">
              <Youtube size={20} />
            </a>
          </div>
        </div>

        <div>
          <h3 className="text-lg font-medium mb-4">Quick Links:</h3>
          <ul className="space-y-2">
            <li>
              <Link href="/" className="text-teal-100 hover:text-white transition-colors">
                Home
              </Link>
            </li>
            <li>
              <Link href="/find-teacher" className="text-teal-100 hover:text-white transition-colors">
                Find a Teacher
              </Link>
            </li>
            <li>
              <Link href="/how-it-works" className="text-teal-100 hover:text-white transition-colors">
                How It Works
              </Link>
            </li>
            <li>
              <Link href="/blog" className="text-teal-100 hover:text-white transition-colors">
                Blog
              </Link>
            </li>
            <li>
              <Link href="/about" className="text-teal-100 hover:text-white transition-colors">
                About Us
              </Link>
            </li>
          </ul>
        </div>

        <div>
          <h3 className="text-lg font-medium mb-4">Features</h3>
          <ul className="space-y-2">
            <li>
              <Link href="#" className="text-teal-100 hover:text-white transition-colors">
                Top-Rated Quran Teachers
              </Link>
            </li>
            <li>
              <Link href="#" className="text-teal-100 hover:text-white transition-colors">
                Flexible Scheduling
              </Link>
            </li>
            <li>
              <Link href="#" className="text-teal-100 hover:text-white transition-colors">
                Secure Payments
              </Link>
            </li>
            <li>
              <Link href="#" className="text-teal-100 hover:text-white transition-colors">
                Video Lessons
              </Link>
            </li>
          </ul>
        </div>

        <div>
          <h3 className="text-lg font-medium mb-4">Contacts us</h3>
          <ul className="space-y-2">
            <li className="flex items-start gap-2">
              <span aria-hidden="true">📧</span>
              <span>support@iqr.com</span>
            </li>
            <li className="flex items-start gap-2">
              <span aria-hidden="true">📞</span>
              <span>09029939361</span>
            </li>
            <li className="flex items-start gap-2">
              <span aria-hidden="true">📍</span>
              <span>123 Quran Learning St., Lagos, Nigeria</span>
            </li>
          </ul>
        </div>
      </div>
    </footer>
  )
}

