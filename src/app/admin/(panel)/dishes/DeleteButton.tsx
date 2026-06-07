"use client";

import { useTransition } from "react";
import { deleteDish } from "./actions";

export default function DeleteButton({
  id,
  name,
}: {
  id: string;
  name: string;
}) {
  const [pending, start] = useTransition();

  return (
    <button
      type="button"
      disabled={pending}
      onClick={() => {
        if (confirm(`Удалить блюдо «${name}»?`)) {
          start(() => {
            deleteDish(id);
          });
        }
      }}
      className="flex h-8 w-8 items-center justify-center rounded-[7px] text-muted transition-colors hover:bg-[#d98c6a]/12 hover:text-[#c0613c] disabled:opacity-40"
      aria-label="Удалить"
      title="Удалить"
    >
      <svg viewBox="0 0 24 24" className="h-[15px] w-[15px] fill-current" aria-hidden>
        <path d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM19 4h-3.5l-1-1h-5l-1 1H5v2h14V4z" />
      </svg>
    </button>
  );
}
