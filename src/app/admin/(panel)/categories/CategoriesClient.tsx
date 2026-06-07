"use client";

import { useState, useTransition } from "react";
import {
  createCategory,
  updateCategory,
  deleteCategory,
  reorderCategories,
} from "./actions";

type Cat = { id: string; title: string; sort_order: number; count: number };

const inputCls =
  "w-full rounded-[8px] border border-black/10 bg-[#faf9f6] px-3.5 py-2.5 text-[14px] text-ink outline-none transition focus:border-brass focus:bg-white focus:ring-2 focus:ring-brass/15";
const labelCls = "mb-1.5 block text-[12px] font-medium text-ink";

export default function CategoriesClient({
  initialCategories,
}: {
  initialCategories: Cat[];
}) {
  const [cats, setCats] = useState<Cat[]>(initialCategories);
  const [editing, setEditing] = useState<string | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [, startTransition] = useTransition();

  function move(index: number, dir: -1 | 1) {
    const target = index + dir;
    if (target < 0 || target >= cats.length) return;
    const next = [...cats];
    [next[index], next[target]] = [next[target], next[index]];
    setCats(next);
    startTransition(() => {
      reorderCategories(next.map((c) => c.id));
    });
  }

  return (
    <div className="grid gap-6 lg:grid-cols-[1fr_320px]">
      {/* Список */}
      <div>
        {cats.length === 0 ? (
          <div className="rounded-[14px] border border-dashed border-black/15 bg-white/50 py-14 text-center text-[14px] text-muted">
            Категорий пока нет. Добавьте первую справа.
          </div>
        ) : (
          <div className="space-y-2">
            {cats.map((c, i) =>
              editing === c.id ? (
                <form
                  key={c.id}
                  action={async (fd) => {
                    await updateCategory(fd);
                    setEditing(null);
                    startTransition(() =>
                      setCats((prev) =>
                        prev.map((x) =>
                          x.id === c.id
                            ? { ...x, title: fd.get("title") as string }
                            : x,
                        ),
                      ),
                    );
                  }}
                  className="flex items-center gap-2.5 rounded-[12px] border border-brass/40 bg-white px-3.5 py-3 shadow-[0_2px_10px_rgba(33,29,23,0.06)]"
                >
                  <input type="hidden" name="id" value={c.id} />
                  <input
                    name="title"
                    defaultValue={c.title}
                    required
                    // eslint-disable-next-line jsx-a11y/no-autofocus
                    autoFocus
                    className="flex-1 rounded-[6px] border border-black/10 bg-[#faf9f6] px-3 py-1.5 text-[14px] outline-none focus:border-brass"
                  />
                  <button
                    type="submit"
                    className="rounded-[6px] bg-ink px-3.5 py-1.5 text-[12px] font-semibold text-paper"
                  >
                    Сохранить
                  </button>
                  <button
                    type="button"
                    onClick={() => setEditing(null)}
                    className="px-1 text-[13px] text-muted hover:text-ink"
                  >
                    Отмена
                  </button>
                </form>
              ) : (
                <div
                  key={c.id}
                  className="flex items-center gap-3 rounded-[12px] border border-black/[0.06] bg-white px-3.5 py-3 shadow-[0_1px_4px_rgba(33,29,23,0.04)] transition-shadow hover:shadow-[0_3px_12px_rgba(33,29,23,0.08)]"
                >
                  {/* Стрелки порядка */}
                  <div className="flex shrink-0 flex-col">
                    <button
                      onClick={() => move(i, -1)}
                      disabled={i === 0}
                      className="flex h-5 w-6 items-center justify-center rounded-[5px] text-muted transition-colors hover:bg-paper hover:text-brass disabled:opacity-25 disabled:hover:bg-transparent"
                      aria-label="Выше"
                      title="Поднять"
                    >
                      <svg viewBox="0 0 24 24" className="h-3.5 w-3.5 fill-current" aria-hidden>
                        <path d="M7 14l5-5 5 5z" />
                      </svg>
                    </button>
                    <button
                      onClick={() => move(i, 1)}
                      disabled={i === cats.length - 1}
                      className="flex h-5 w-6 items-center justify-center rounded-[5px] text-muted transition-colors hover:bg-paper hover:text-brass disabled:opacity-25 disabled:hover:bg-transparent"
                      aria-label="Ниже"
                      title="Опустить"
                    >
                      <svg viewBox="0 0 24 24" className="h-3.5 w-3.5 fill-current" aria-hidden>
                        <path d="M7 10l5 5 5-5z" />
                      </svg>
                    </button>
                  </div>

                  <div className="min-w-0 flex-1">
                    <p className="font-display text-[16px] font-semibold text-ink">
                      {c.title}
                    </p>
                    <p className="text-[11.5px] text-muted">
                      {c.count} {c.count === 1 ? "блюдо" : "блюд"}
                    </p>
                  </div>
                  <button
                    onClick={() => setEditing(c.id)}
                    className="flex h-8 w-8 items-center justify-center rounded-[7px] text-muted transition-colors hover:bg-paper hover:text-brass"
                    aria-label="Редактировать"
                    title="Редактировать"
                  >
                    <svg viewBox="0 0 24 24" className="h-[15px] w-[15px] fill-current" aria-hidden>
                      <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25zM20.71 7.04c.39-.39.39-1.02 0-1.41l-2.34-2.34a.9959.9959 0 00-1.41 0l-1.83 1.83 3.75 3.75 1.83-1.83z" />
                    </svg>
                  </button>
                  <button
                    onClick={async () => {
                      if (
                        confirm(
                          c.count > 0
                            ? `В категории «${c.title}» ${c.count} блюд — они тоже удалятся. Продолжить?`
                            : `Удалить категорию «${c.title}»?`,
                        )
                      ) {
                        await deleteCategory(c.id);
                        setCats((prev) => prev.filter((x) => x.id !== c.id));
                      }
                    }}
                    className="flex h-8 w-8 items-center justify-center rounded-[7px] text-muted transition-colors hover:bg-[#d98c6a]/12 hover:text-[#c0613c]"
                    aria-label="Удалить"
                    title="Удалить"
                  >
                    <svg viewBox="0 0 24 24" className="h-[15px] w-[15px] fill-current" aria-hidden>
                      <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
                    </svg>
                  </button>
                </div>
              ),
            )}
          </div>
        )}
      </div>

      {/* Добавить */}
      <div className="lg:sticky lg:top-24 lg:self-start">
        <div className="rounded-[14px] border border-black/[0.06] bg-white p-6 shadow-[0_2px_10px_rgba(33,29,23,0.04)]">
          <h2 className="mb-5 text-[11px] font-semibold uppercase tracking-[0.18em] text-brass">
            Новая категория
          </h2>
          <form
            id="new-cat-form"
            action={async (fd) => {
              setError(null);
              const res = await createCategory(fd);
              if (res?.error) {
                setError(res.error);
                return;
              }
              startTransition(() =>
                setCats((prev) => [
                  ...prev,
                  {
                    id: res.id!,
                    title: fd.get("title") as string,
                    sort_order: prev.length + 1,
                    count: 0,
                  },
                ]),
              );
              (document.getElementById("new-cat-form") as HTMLFormElement)?.reset();
            }}
            className="space-y-4"
          >
            <div>
              <label className={labelCls}>Название категории</label>
              <input name="title" required placeholder="Напитки" className={inputCls} />
            </div>

            {error && <p className="text-[12.5px] text-[#c0613c]">{error}</p>}

            <button
              type="submit"
              className="w-full rounded-[8px] bg-ink py-2.5 text-[13px] font-semibold text-paper transition-opacity hover:opacity-90"
            >
              Добавить категорию
            </button>
            <p className="text-[11.5px] text-muted">
              Новая категория добавится в конец. Порядок меняйте стрелками слева.
            </p>
          </form>
        </div>
      </div>
    </div>
  );
}
