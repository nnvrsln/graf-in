"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import Image from "next/image";
import Link from "next/link";
import { supabase } from "@/lib/supabase";
import type { Category } from "@/data/menu";
import type { SiteSettings } from "@/lib/settings";
import MenuView from "./menu/MenuView";

// Стили mask для логотипа «Графин» (фиолетовый SVG → перекрашиваем в бумагу)
const logoMask = {
  aspectRatio: "429 / 191",
  WebkitMaskImage: "url(/logo.svg)",
  maskImage: "url(/logo.svg)",
  WebkitMaskRepeat: "no-repeat",
  maskRepeat: "no-repeat",
  WebkitMaskSize: "contain",
  maskSize: "contain",
} as const;

export default function WelcomeView({ settings }: { settings: SiteSettings }) {
  const router = useRouter();
  const [leaving, setLeaving] = useState(false);
  const [menuCategories, setMenuCategories] = useState<Category[]>([]);

  // Грузим данные меню в фоне, пока пользователь на приветствии —
  // чтобы подложка была готова к моменту анимации перехода
  useEffect(() => {
    router.prefetch("/menu");

    async function prefetchMenu() {
      const { data: cats } = await supabase
        .from("categories")
        .select("id, title, sort_order")
        .order("sort_order");
      if (!cats?.length) return;

      const { data: dishes } = await supabase
        .from("dishes")
        .select("id, category_id, name, description, price, weight, photo, badge, kcal, protein, fat, carbs, composition, allergens, sort_order")
        .order("sort_order");

      setMenuCategories(
        cats.map((c) => ({
          id: c.id,
          title: c.title,
          index: String(c.sort_order).padStart(2, "0"),
          dishes: (dishes ?? [])
            .filter((d) => d.category_id === c.id)
            .map((d) => ({
              name: d.name,
              desc: d.description ?? "",
              price: d.price,
              weight: d.weight ?? "",
              photo: d.photo ?? "",
              badge: d.badge ?? undefined,
              kcal: d.kcal ?? undefined,
              protein: d.protein ?? undefined,
              fat: d.fat ?? undefined,
              carbs: d.carbs ?? undefined,
              composition: d.composition ?? undefined,
              allergens: d.allergens ?? undefined,
            })),
        }))
      );
    }

    prefetchMenu();
  }, [router]);

  // По клику приветствие уезжает вверх, затем переходим в меню.
  // Если JS не успел — <Link> отработает обычным переходом (без анимации).
  const handleMenu = (e: React.MouseEvent) => {
    e.preventDefault();
    if (leaving) return;
    setLeaving(true);
    setTimeout(() => router.push("/menu"), 680);
  };

  return (
    <>
      {/* ───────── Прелоадер (чистый CSS, уезжает вверх) ───────── */}
      <div className="preloader" aria-hidden>
        <div className="preloader-glow" />
        <div
          className="preloader-logo w-[230px] max-w-[70%] bg-paper"
          style={logoMask}
        />
      </div>

      {/* Меню-подложка — проявляется, когда приветствие уезжает вверх */}
      {leaving && (
        <div className="fixed inset-0 z-0 overflow-y-auto">
          <MenuView categories={menuCategories} />
        </div>
      )}

      {/* ───────── Приветствие ───────── */}
      <main
        className={`relative z-10 flex min-h-dvh flex-col justify-end overflow-hidden bg-dark text-paper ${
          leaving ? "welcome-leaving" : ""
        }`}
      >
        {/* Видео-фон зала (web-форматы из bg.MOV). Постер на случай энергосбережения. */}
        <video
          className="absolute inset-0 h-full w-full object-cover"
          autoPlay
          muted
          loop
          playsInline
          preload="metadata"
          poster="/poster.jpg"
        >
          <source src="/bg.webm" type="video/webm" />
          <source src="/bg.mp4" type="video/mp4" />
        </video>

        {/* Затемнение */}
        <div
          className="absolute inset-0"
          style={{
            background:
              "linear-gradient(180deg, rgba(15,13,11,.92) 0%, rgba(15,13,11,.55) 13%, rgba(15,13,11,.34) 34%, rgba(15,13,11,.66) 70%, rgba(15,13,11,.9) 100%), rgba(15,13,11,.12)",
          }}
        />

        {/* Мини-логотип (знак) сверху */}
        <div
          className="reveal absolute inset-x-0 top-[calc(env(safe-area-inset-top,0px)+1.75rem)] z-10 flex flex-col items-center"
          style={{ animationDelay: "0.1s" }}
        >
          {/* eslint-disable-next-line @next/next/no-img-element */}
          <img src="/znak.svg" alt="" className="h-14 w-auto opacity-90" />
          <div className="mt-3 text-[9px] uppercase tracking-[0.32em] text-paper/80">
            Est. 2024
          </div>
        </div>

        {/* Контент — прижат вниз (как в превью v4) */}
        <div className="relative z-10 px-7 pt-10 pb-[calc(env(safe-area-inset-bottom,0px)+2rem)]">
          {/* Трио — по центру по горизонтали */}
          <p
            className="reveal mb-3 text-center text-[10px] uppercase tracking-[0.26em] text-gold"
            style={{ animationDelay: "0.18s" }}
          >
            Авторская кухня
          </p>
          {/* Логотип «Графин» вместо текстового заголовка */}
          <div className="reveal relative" style={{ animationDelay: "0.26s" }}>
            {/* Мягкое золотое сияние за логотипом */}
            <div
              aria-hidden
              className="pointer-events-none absolute left-1/2 top-1/2 h-44 w-[300px] max-w-[90%] -translate-x-1/2 -translate-y-1/2 blur-3xl"
              style={{
                background:
                  "radial-gradient(ellipse at center, rgba(216,184,119,0.42) 0%, rgba(216,184,119,0.14) 45%, transparent 72%)",
              }}
            />
            <div
              role="img"
              aria-label={settings.restaurant_name}
              className="relative mx-auto w-[245px] max-w-[76%] bg-paper"
              style={logoMask}
            />
          </div>
          <p
            className="reveal mt-6 text-center font-display text-[15px] italic leading-snug text-paper/85"
            style={{ animationDelay: "0.36s" }}
          >
            {settings.tagline}
          </p>

          {/* Шеф-повар — фото слева, текст справа */}
          <div
            className="reveal mt-8 flex items-center justify-center gap-4"
            style={{ animationDelay: "0.56s" }}
          >
            <div className="flex-none rounded-full border border-gold/40 p-1">
              <Image
                src={settings.chef_photo}
                alt={settings.chef_name}
                width={120}
                height={120}
                className="h-[68px] w-[68px] rounded-full object-cover"
                unoptimized
              />
            </div>
            <div className="max-w-[180px] text-left">
              <span className="block text-[9px] uppercase tracking-[0.3em] text-gold/90">
                Шеф-повар
              </span>
              <p className="mt-1.5 font-display text-[20px] leading-none text-paper">
                {settings.chef_name}
              </p>
              <p className="mt-2 text-[10px] uppercase tracking-[0.18em] text-paper/55">
                {settings.chef_title}
              </p>
            </div>
          </div>

          {/* CTA — редакционная рамка + золотое свечение */}
          <Link
            href="/menu"
            onClick={handleMenu}
            className="reveal group mt-8 flex items-center justify-center gap-3 rounded-[4px] border border-gold/55 py-[18px] text-[11px] font-semibold uppercase tracking-[0.28em] text-gold shadow-[0_12px_34px_-18px_rgba(216,184,119,0.28)] transition-colors duration-200 hover:bg-gold/10"
            style={{ animationDelay: "0.66s" }}
          >
            Смотреть меню
            <span
              aria-hidden
              className="transition-transform duration-200 group-hover:translate-x-1"
            >
              →
            </span>
          </Link>
        </div>
      </main>
    </>
  );
}
