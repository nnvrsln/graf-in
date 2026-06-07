import type { Metadata, Viewport } from "next";
import { Playfair_Display, Manrope } from "next/font/google";
import "./globals.css";

const playfair = Playfair_Display({
  subsets: ["latin", "cyrillic"],
  weight: ["500", "600", "700", "800"],
  style: ["normal", "italic"],
  variable: "--font-playfair",
  display: "swap",
});

const manrope = Manrope({
  subsets: ["latin", "cyrillic"],
  weight: ["400", "500", "600", "700"],
  variable: "--font-manrope",
  display: "swap",
});

export const metadata: Metadata = {
  title: "Графин — авторская кухня",
  description: "Меню ресторана «Графин». Сезонная авторская кухня.",
};

export const viewport: Viewport = {
  themeColor: "#0f0d0b",
  width: "device-width",
  initialScale: 1,
  maximumScale: 1,
  viewportFit: "cover",
};

export default function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  return (
    <html
      lang="ru"
      data-scroll-behavior="smooth"
      className={`${playfair.variable} ${manrope.variable} h-full antialiased`}
    >
      {/* Оболочка тёмная: на iOS оверскролл/safe-area не белеют на приветствии.
          Светлые страницы (меню) красят свой фон сами. */}
      <body className="min-h-full bg-dark text-ink">
        {children}
      </body>
    </html>
  );
}
