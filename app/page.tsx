import { Header } from "@/components/layout/header"
import { HeroSection } from "@/components/home/hero-section"
import { HowItWorksSection } from "@/components/home/how-it-works"
import { TeachersSection } from "@/components/home/teachers-section"
import { TestimonialsSection } from "@/components/home/testimonials-section"
import { BecomeTeacherSection } from "@/components/home/become-teacher-section"
import { FAQSection } from "@/components/home/faq-section"
import { CTASection } from "@/components/home/cta-section"
import { Footer } from "@/components/layout/footer"

export default function Home() {
  return (
    <main className="min-h-screen flex flex-col">
      <Header />
      <HeroSection />
      <HowItWorksSection />
      <TeachersSection />
      <TestimonialsSection />
      <BecomeTeacherSection />
      <FAQSection />
      <CTASection />
      <Footer />
    </main>
  )
}

