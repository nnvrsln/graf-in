"use client";

import { useState } from "react";
import Image from "next/image";
import Link from "next/link";
import type { Category } from "@/data/menu";
import { saveDish } from "./actions";

// Сжимаем/уменьшаем фото прямо в браузере до отправки в server action.
// Иначе фото с телефона (2–8 MB) превышает лимит тела запроса Next/Vercel
// и сохранение падает с 500. Уменьшаем до 1600px по длинной стороне и жмём в JPEG.
async function compressImage(
  file: File,
  maxDim = 1600,
  quality = 0.82,
): Promise<File> {
  const dataUrl = await new Promise<string>((resolve, reject) => {
    const reader = new FileReader();
    reader.onload = () => resolve(reader.result as string);
    reader.onerror = () => reject(new Error("read failed"));
    reader.readAsDataURL(file);
  });

  const img = document.createElement("img");
  await new Promise<void>((resolve, reject) => {
    img.onload = () => resolve();
    img.onerror = () => reject(new Error("decode failed"));
    img.src = dataUrl;
  });

  let width = img.naturalWidth;
  let height = img.naturalHeight;
  if (width > maxDim || height > maxDim) {
    const scale = Math.min(maxDim / width, maxDim / height);
    width = Math.round(width * scale);
    height = Math.round(height * scale);
  }

  const canvas = document.createElement("canvas");
  canvas.width = width;
  canvas.height = height;
  const ctx = canvas.getContext("2d");
  if (!ctx) return file;
  ctx.drawImage(img, 0, 0, width, height);

  const blob = await new Promise<Blob | null>((resolve) =>
    canvas.toBlob(resolve, "image/jpeg", quality),
  );
  if (!blob) return file;

  const baseName = file.name.replace(/\.[^.]+$/, "") || "photo";
  return new File([blob], `${baseName}.jpg`, { type: "image/jpeg" });
}

type DishData = {
  id?: string;
  name?: string;
  description?: string;
  price?: string;
  weight?: string;
  category_id?: string;
  badge?: string;
  photo?: string;
  kcal?: number;
  protein?: number;
  fat?: number;
  carbs?: number;
  composition?: string;
  allergens?: string[];
  sort_order?: number;
};

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

export default function DishForm({
  dish,
  categories,
}: {
  dish?: DishData;
  categories: Pick<Category, "id" | "title">[];
}) {
  const [preview, setPreview] = useState<string | null>(dish?.photo ?? null);
  const [pending, setPending] = useState(false);
  const [compressing, setCompressing] = useState(false);

  return (
    <form
      action={saveDish}
      onSubmit={() => setPending(true)}
      className="space-y-5"
    >
      {dish?.id && <input type="hidden" name="id" value={dish.id} />}
      <input type="hidden" name="photo_url" value={preview ?? ""} />

      <div className="grid gap-5 lg:grid-cols-[1fr_300px]">
        {/* Левая колонка */}
        <div className="space-y-5">
          <Section title="Основное">
            <div className="space-y-4">
              <div>
                <label className={labelCls}>Название *</label>
                <input name="name" required defaultValue={dish?.name} className={inputCls} />
              </div>
              <div>
                <label className={labelCls}>Описание</label>
                <textarea
                  name="description"
                  defaultValue={dish?.description}
                  rows={2}
                  className={inputCls}
                  placeholder="Вырезка, перепелиный желток, каперсы, бриошь"
                />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className={labelCls}>Цена *</label>
                  <input
                    name="price"
                    required
                    defaultValue={dish?.price}
                    placeholder="890 ₽"
                    className={inputCls}
                  />
                </div>
                <div>
                  <label className={labelCls}>Граммовка</label>
                  <input
                    name="weight"
                    defaultValue={dish?.weight}
                    placeholder="120 г"
                    className={inputCls}
                  />
                </div>
              </div>
            </div>
          </Section>

          <Section title="Пищевая ценность">
            <div className="grid grid-cols-2 gap-4 sm:grid-cols-4">
              {(
                [
                  ["kcal", "Ккал"],
                  ["protein", "Белки, г"],
                  ["fat", "Жиры, г"],
                  ["carbs", "Углев., г"],
                ] as const
              ).map(([name, label]) => (
                <div key={name}>
                  <label className={labelCls}>{label}</label>
                  <input
                    name={name}
                    type="number"
                    defaultValue={dish?.[name as keyof DishData] as number | undefined}
                    className={inputCls}
                  />
                </div>
              ))}
            </div>
          </Section>

          <Section title="Состав и аллергены">
            <div className="space-y-4">
              <div>
                <label className={labelCls}>Состав</label>
                <textarea
                  name="composition"
                  defaultValue={dish?.composition}
                  rows={3}
                  className={inputCls}
                  placeholder="Полный список ингредиентов через запятую"
                />
              </div>
              <div>
                <label className={labelCls}>Аллергены</label>
                <input
                  name="allergens"
                  defaultValue={dish?.allergens?.join(", ")}
                  placeholder="Глютен, Молоко, Яйцо"
                  className={inputCls}
                />
                <p className="mt-1.5 text-[11.5px] text-muted">
                  Перечислите через запятую — на сайте они покажутся в блоке-предупреждении.
                </p>
              </div>
            </div>
          </Section>
        </div>

        {/* Правая колонка */}
        <div className="space-y-5">
          <Section title="Фотография">
            <div className="relative mb-3 aspect-[4/3] overflow-hidden rounded-[10px] border border-black/[0.06] bg-paper-2">
              {preview ? (
                <Image
                  src={preview}
                  alt=""
                  fill
                  sizes="300px"
                  className="object-cover"
                  unoptimized={preview.startsWith("blob:")}
                />
              ) : (
                <div className="flex h-full w-full flex-col items-center justify-center gap-2 text-muted/40">
                  <svg viewBox="0 0 24 24" className="h-9 w-9 fill-current" aria-hidden>
                    <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z" />
                  </svg>
                  <span className="text-[11px]">Нет фото</span>
                </div>
              )}
            </div>
            <label className="block cursor-pointer rounded-[8px] border border-dashed border-black/15 px-3 py-2.5 text-center text-[12.5px] text-brass transition-colors hover:border-brass/50 hover:bg-paper/40">
              {compressing
                ? "Обработка фото…"
                : preview
                  ? "Заменить фото"
                  : "Загрузить фото"}
              <input
                type="file"
                name="photo"
                accept="image/*"
                className="hidden"
                onChange={async (e) => {
                  const input = e.target;
                  const file = input.files?.[0];
                  if (!file) return;
                  setCompressing(true);
                  try {
                    const compressed = await compressImage(file);
                    // Подменяем содержимое инпута сжатым файлом, чтобы форма
                    // отправила именно его (а не тяжёлый оригинал).
                    const dt = new DataTransfer();
                    dt.items.add(compressed);
                    input.files = dt.files;
                    setPreview(URL.createObjectURL(compressed));
                  } catch {
                    // Не смогли сжать (напр. неподдерживаемый формат) — шлём как есть.
                    setPreview(URL.createObjectURL(file));
                  } finally {
                    setCompressing(false);
                  }
                }}
              />
            </label>
            <p className="mt-2 text-center text-[11px] text-muted">
              JPG, PNG, WebP
            </p>
          </Section>

          <Section title="Параметры">
            <div className="space-y-4">
              <div>
                <label className={labelCls}>Категория *</label>
                <select
                  name="category_id"
                  required
                  defaultValue={dish?.category_id ?? ""}
                  className={inputCls}
                >
                  <option value="">— выберите —</option>
                  {categories.map((c) => (
                    <option key={c.id} value={c.id}>
                      {c.title}
                    </option>
                  ))}
                </select>
              </div>
              <div>
                <label className={labelCls}>Бейдж</label>
                <select
                  name="badge"
                  defaultValue={dish?.badge ?? ""}
                  className={inputCls}
                >
                  <option value="">Нет</option>
                  <option value="hit">Хит</option>
                  <option value="hot">Остро</option>
                  <option value="veg">Веган</option>
                </select>
              </div>
            </div>
          </Section>
        </div>
      </div>

      {/* Кнопки — липкая панель снизу */}
      <div className="sticky bottom-0 -mx-5 flex items-center justify-end gap-3 border-t border-black/[0.07] bg-[#f4f1ea]/95 px-5 py-4 backdrop-blur">
        <Link
          href="/admin/dishes"
          className="rounded-[8px] px-4 py-2.5 text-[13px] font-medium text-muted transition-colors hover:text-ink"
        >
          Отмена
        </Link>
        <button
          type="submit"
          disabled={pending || compressing}
          className="rounded-[8px] bg-ink px-7 py-2.5 text-[13px] font-semibold text-paper transition-opacity hover:opacity-90 disabled:opacity-50"
        >
          {compressing
            ? "Обработка фото…"
            : pending
              ? "Сохранение…"
              : dish?.id
                ? "Сохранить"
                : "Добавить блюдо"}
        </button>
      </div>
    </form>
  );
}
