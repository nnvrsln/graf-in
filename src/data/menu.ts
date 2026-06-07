// Моковые данные меню. На следующем шаге заменим на загрузку из Supabase.

export type Badge = "hit" | "hot" | "veg";

export type Dish = {
  name: string;
  desc: string;
  price: string;
  weight: string;
  photo: string; // id фото Unsplash
  badge?: Badge;
  // Детальная информация (для разворота карточки)
  kcal?: number; // ккал на порцию
  protein?: number; // белки, г
  fat?: number; // жиры, г
  carbs?: number; // углеводы, г
  composition?: string; // полный состав
  allergens?: string[]; // аллергены: ["Глютен", "Молоко", ...]
};

export type Category = {
  id: string;
  title: string;
  index: string;
  dishes: Dish[];
};

// Фото блюда: мелкое для карточки и крупное для детальной шторки
// Если id — полный URL (загруженное фото), возвращаем как есть
export const cardImg = (id: string) =>
  id.startsWith("http")
    ? id
    : `https://images.unsplash.com/${id}?auto=format&fit=crop&w=200&h=200&q=72`;
export const heroImg = (id: string) =>
  id.startsWith("http")
    ? id
    : `https://images.unsplash.com/${id}?auto=format&fit=crop&w=900&h=640&q=80`;

export const categories: Category[] = [
  {
    id: "zakuski",
    title: "Закуски",
    index: "01",
    dishes: [
      {
        name: "Тартар из говядины",
        desc: "Вырезка, перепелиный желток, каперсы, бриошь",
        price: "890 ₽",
        weight: "120 г",
        badge: "hit",
        photo: "photo-1546069901-ba9599a7e63c",
        kcal: 320,
        protein: 24,
        fat: 19,
        carbs: 12,
        composition:
          "Говяжья вырезка, перепелиный желток, каперсы, дижонская горчица, бриошь, оливковое масло, лук-шалот, зелень.",
        allergens: ["Глютен", "Яйцо", "Горчица"],
      },
      {
        name: "Севиче из сибаса",
        desc: "Лайм, чили, кинза, авокадо, лук-шалот",
        price: "920 ₽",
        weight: "110 г",
        badge: "hot",
        photo: "photo-1559339352-11d035aa65de",
        kcal: 210,
        protein: 22,
        fat: 9,
        carbs: 7,
        composition:
          "Филе сибаса, сок лайма, перец чили, кинза, авокадо, лук-шалот, оливковое масло, морская соль.",
        allergens: ["Рыба"],
      },
      {
        name: "Хумус с томлёной свёклой",
        desc: "Нут, тахини, печёная свёкла, лепёшка",
        price: "540 ₽",
        weight: "180 г",
        badge: "veg",
        photo: "photo-1540189549336-e6e99c3679fe",
        kcal: 290,
        protein: 9,
        fat: 14,
        carbs: 32,
        composition:
          "Нут, паста тахини, печёная свёкла, чеснок, сок лимона, оливковое масло, зира, пшеничная лепёшка.",
        allergens: ["Глютен", "Кунжут"],
      },
      {
        name: "Карпаччо из белых грибов",
        desc: "Трюфельное масло, пармезан, руккола",
        price: "760 ₽",
        weight: "90 г",
        photo: "photo-1432139555190-58524dae6a55",
        kcal: 180,
        protein: 7,
        fat: 14,
        carbs: 5,
        composition:
          "Белые грибы, трюфельное масло, пармезан, руккола, сок лимона, чёрный перец.",
        allergens: ["Молоко"],
      },
    ],
  },
  {
    id: "salaty",
    title: "Салаты",
    index: "02",
    dishes: [
      {
        name: "Зелёный с авокадо",
        desc: "Микс салатов, авокадо, огурец, кунжутная заправка",
        price: "620 ₽",
        weight: "160 г",
        badge: "veg",
        photo: "photo-1505253716362-afaea1d3d1af",
        kcal: 240,
        protein: 5,
        fat: 18,
        carbs: 14,
        composition:
          "Микс салатов, авокадо, огурец, эдамаме, кунжутная заправка, семена кунжута, лайм.",
        allergens: ["Кунжут", "Соя"],
      },
      {
        name: "Тёплый салат с уткой",
        desc: "Конфи из утки, груша, орех, бальзамик",
        price: "780 ₽",
        weight: "190 г",
        photo: "photo-1551248429-40975aa4de74",
        kcal: 360,
        protein: 19,
        fat: 24,
        carbs: 16,
        composition:
          "Конфи из утиной ножки, груша, грецкий орех, микс салатов, бальзамический соус, оливковое масло.",
        allergens: ["Орехи"],
      },
    ],
  },
  {
    id: "osnovnye",
    title: "Основные",
    index: "03",
    dishes: [
      {
        name: "Утиная грудка",
        desc: "Вишнёвый соус, корень сельдерея, тимьян",
        price: "1 290 ₽",
        weight: "220 г",
        badge: "hit",
        photo: "photo-1467003909585-2f8a72700288",
        kcal: 520,
        protein: 34,
        fat: 32,
        carbs: 18,
        composition:
          "Утиная грудка, вишнёвый соус, пюре из корня сельдерея, тимьян, сливочное масло, демигляс.",
        allergens: ["Молоко"],
      },
      {
        name: "Чёрная треска мисо",
        desc: "Маринад мисо 48 ч, понзу, зелёный лук",
        price: "1 540 ₽",
        weight: "180 г",
        photo: "photo-1414235077428-338989a2e8c0",
        kcal: 410,
        protein: 30,
        fat: 26,
        carbs: 9,
        composition:
          "Чёрная треска, паста мисо, мирин, саке, соус понзу, зелёный лук, кунжут.",
        allergens: ["Рыба", "Соя", "Кунжут"],
      },
      {
        name: "Ризотто с лесными грибами",
        desc: "Карнароли, белые грибы, трюфель",
        price: "980 ₽",
        weight: "240 г",
        badge: "veg",
        photo: "photo-1476224203421-9ac39bcb3327",
        kcal: 480,
        protein: 12,
        fat: 22,
        carbs: 56,
        composition:
          "Рис карнароли, белые грибы, шампиньоны, трюфельное масло, пармезан, сливочное масло, овощной бульон, лук.",
        allergens: ["Молоко"],
      },
    ],
  },
  {
    id: "deserty",
    title: "Десерты",
    index: "04",
    dishes: [
      {
        name: "Тарт с грушей",
        desc: "Песочное тесто, карамель, ваниль",
        price: "520 ₽",
        weight: "140 г",
        photo: "photo-1488477181946-6428a0291777",
        kcal: 430,
        protein: 6,
        fat: 21,
        carbs: 54,
        composition:
          "Песочное тесто, груша, солёная карамель, ванильный крем, сливочное масло, миндальная мука.",
        allergens: ["Глютен", "Молоко", "Яйцо", "Орехи"],
      },
      {
        name: "Шоколадный фондан",
        desc: "Тёплый шоколад, мороженое, малина",
        price: "560 ₽",
        weight: "150 г",
        badge: "hit",
        photo: "photo-1606313564200-e75d5e30476c",
        kcal: 540,
        protein: 8,
        fat: 30,
        carbs: 58,
        composition:
          "Тёмный шоколад 70%, сливочное масло, яйцо, сахар, мука, ванильное мороженое, свежая малина.",
        allergens: ["Глютен", "Молоко", "Яйцо"],
      },
    ],
  },
];
