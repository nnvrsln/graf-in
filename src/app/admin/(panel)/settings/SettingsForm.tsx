"use client";

import { useState } from "react";
import Image from "next/image";
import { saveSettings } from "./actions";

const inputCls =
  "w-full rounded-[8px] border border-black/10 bg-[#faf9f6] px-3.5 py-2.5 text-[14px] text-ink outline-none transition focus:border-brass focus:bg-white focus:ring-2 focus:ring-brass/15";
const labelCls = "mb-1.5 block text-[12px] font-medium text-ink";

function Section({
  title,
  children,
}: {
  title: string;
  children: React.ReactNode;
}) {
  return (
    <section className="rounded-[14px] border border-black/[0.06] bg-white p-6 shadow-[0_2px_10px_rgba(33,29,23,0.04)]">
      <h2 className="mb-5 text-[11px] font-semibold uppercase tracking-[0.18em] text-brass">
        {title}
      </h2>
      {children}
    </section>
  );
}

export default function SettingsForm({
  settings,
}: {
  settings: Record<string, string>;
}) {
  const [chefPreview, setChefPreview] = useState<string | null>(
    settings.chef_photo ?? null,
  );
  const [saved, setSaved] = useState(false);
  const [pending, setPending] = useState(false);

  return (
    <form
      action={async (fd) => {
        setPending(true);
        await saveSettings(fd);
        setPending(false);
        setSaved(true);
        setTimeout(() => setSaved(false), 3000);
      }}
      className="max-w-2xl space-y-5"
    >
      <input type="hidden" name="chef_photo" value={chefPreview ?? ""} />

      <Section title="Ресторан">
        <div className="space-y-4">
          <div>
            <label className={labelCls}>Название</label>
            <input
              name="restaurant_name"
              defaultValue={settings.restaurant_name ?? ""}
              className={inputCls}
            />
          </div>
          <div>
            <label className={labelCls}>Слоган (приветственная страница)</label>
            <textarea
              name="tagline"
              defaultValue={settings.tagline ?? ""}
              rows={3}
              className={inputCls}
            />
            <p className="mt-1.5 text-[11.5px] text-muted">
              Курсивная фраза под логотипом на входе в меню.
            </p>
          </div>
        </div>
      </Section>

      <Section title="Шеф-повар">
        <div className="flex flex-col gap-5 sm:flex-row">
          {/* Фото */}
          <div className="flex flex-col items-center gap-3">
            <div className="relative h-28 w-28 overflow-hidden rounded-full border border-black/[0.06] bg-paper-2 ring-1 ring-gold/20">
              {chefPreview ? (
                <Image
                  src={chefPreview}
                  alt="Шеф"
                  fill
                  sizes="112px"
                  className="object-cover"
                  unoptimized={chefPreview.startsWith("blob:")}
                />
              ) : (
                <div className="flex h-full w-full items-center justify-center text-muted/40">
                  <svg viewBox="0 0 24 24" className="h-10 w-10 fill-current" aria-hidden>
                    <path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                  </svg>
                </div>
              )}
            </div>
            <label className="cursor-pointer text-[12.5px] text-brass transition-colors hover:text-gold">
              Изменить фото
              <input
                type="file"
                name="chef_photo_file"
                accept="image/*"
                className="hidden"
                onChange={(e) => {
                  const file = e.target.files?.[0];
                  if (file) setChefPreview(URL.createObjectURL(file));
                }}
              />
            </label>
          </div>

          {/* Поля */}
          <div className="flex-1 space-y-4">
            <div>
              <label className={labelCls}>Имя</label>
              <input
                name="chef_name"
                defaultValue={settings.chef_name ?? ""}
                className={inputCls}
              />
            </div>
            <div>
              <label className={labelCls}>Должность</label>
              <input
                name="chef_title"
                defaultValue={settings.chef_title ?? ""}
                className={inputCls}
              />
            </div>
          </div>
        </div>
      </Section>

      <div className="sticky bottom-0 -mx-5 flex items-center justify-end gap-4 border-t border-black/[0.07] bg-[#f4f1ea]/95 px-5 py-4 backdrop-blur">
        {saved && (
          <span className="flex items-center gap-1.5 text-[13px] font-medium text-[#5b7a32]">
            <svg viewBox="0 0 24 24" className="h-4 w-4 fill-current" aria-hidden>
              <path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41z" />
            </svg>
            Сохранено
          </span>
        )}
        <button
          type="submit"
          disabled={pending}
          className="rounded-[8px] bg-ink px-7 py-2.5 text-[13px] font-semibold text-paper transition-opacity hover:opacity-90 disabled:opacity-50"
        >
          {pending ? "Сохранение…" : "Сохранить изменения"}
        </button>
      </div>
    </form>
  );
}
