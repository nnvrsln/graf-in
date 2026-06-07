"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";

const items = [
  {
    href: "/admin",
    label: "Обзор",
    exact: true,
    icon: (
      <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
    ),
  },
  {
    href: "/admin/dishes",
    label: "Блюда",
    icon: (
      <path d="M11 9H9V2H7v7H5V2H3v7c0 2.12 1.66 3.84 3.75 3.97V22h2.5v-9.03C11.34 12.84 13 11.12 13 9V2h-2v7zm5-3v8h2.5v8H21V2c-2.76 0-5 2.24-5 4z" />
    ),
  },
  {
    href: "/admin/categories",
    label: "Категории",
    icon: <path d="M3 5h18v2H3V5zm0 6h18v2H3v-2zm0 6h18v2H3v-2z" />,
  },
  {
    href: "/admin/settings",
    label: "Настройки",
    icon: (
      <path d="M19.14 12.94c.04-.3.06-.61.06-.94 0-.32-.02-.64-.07-.94l2.03-1.58a.49.49 0 00.12-.61l-1.92-3.32a.488.488 0 00-.59-.22l-2.39.96c-.5-.38-1.03-.7-1.62-.94l-.36-2.54a.484.484 0 00-.48-.41h-3.84c-.24 0-.43.17-.47.41l-.36 2.54c-.59.24-1.13.57-1.62.94l-2.39-.96c-.22-.08-.47 0-.59.22L2.74 8.87c-.12.21-.08.47.12.61l2.03 1.58c-.05.3-.09.63-.09.94s.02.64.07.94l-2.03 1.58a.49.49 0 00-.12.61l1.92 3.32c.12.22.37.29.59.22l2.39-.96c.5.38 1.03.7 1.62.94l.36 2.54c.05.24.24.41.48.41h3.84c.24 0 .44-.17.47-.41l.36-2.54c.59-.24 1.13-.56 1.62-.94l2.39.96c.22.08.47 0 .59-.22l1.92-3.32c.12-.22.07-.47-.12-.61l-2.01-1.58zM12 15.6c-1.98 0-3.6-1.62-3.6-3.6s1.62-3.6 3.6-3.6 3.6 1.62 3.6 3.6-1.62 3.6-3.6 3.6z" />
    ),
  },
] as const;

export default function AdminNav() {
  const path = usePathname();

  return (
    <nav className="flex items-center gap-1">
      {items.map((it) => {
        const exact = "exact" in it && it.exact;
        const active = exact ? path === it.href : path.startsWith(it.href);
        return (
          <Link
            key={it.href}
            href={it.href}
            className={`flex items-center gap-2 rounded-[7px] px-3 py-2 text-[13px] transition-colors ${
              active
                ? "bg-gold/15 text-gold"
                : "text-paper/55 hover:bg-paper/5 hover:text-paper/90"
            }`}
          >
            <svg
              viewBox="0 0 24 24"
              className="h-[15px] w-[15px] fill-current"
              aria-hidden
            >
              {it.icon}
            </svg>
            {it.label}
          </Link>
        );
      })}
    </nav>
  );
}
