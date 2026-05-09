// src/app/shared/models/user.model.ts
export interface User {
  id: number;
  name: string;
  email: string;
  role: 'user' | 'admin';
  language: string;
  theme: 'light' | 'dark';
  unit: 'metric' | 'imperial';
  created_at: string;
}

// src/app/shared/models/weather.model.ts
export interface CurrentWeather {
  name: string;
  sys: { country: string; sunrise: number; sunset: number };
  coord: { lat: number; lon: number };
  weather: Array<{ id: number; main: string; description: string; icon: string }>;
  main: {
    temp: number; feels_like: number;
    temp_min: number; temp_max: number;
    humidity: number; pressure: number;
  };
  wind: { speed: number; deg: number };
  visibility: number;
  clouds: { all: number };
  dt: number;
}

export interface ForecastItem {
  dt: number;
  main: { temp: number; temp_min: number; temp_max: number; humidity: number };
  weather: Array<{ description: string; icon: string }>;
  wind: { speed: number };
  dt_txt: string;
}

export interface Forecast {
  city: { name: string; country: string };
  list: ForecastItem[];
}

export interface Favorite {
  id: number;
  user_id: number;
  city_name: string;
  country: string;
  lat: number | null;
  lon: number | null;
  created_at: string;
}

export interface SearchHistory {
  id: number;
  city_name: string;
  country: string;
  temp_c: number | null;
  condition: string | null;
  searched_at: string;
}

export interface ApiResponse<T> {
  success: boolean;
  message: string;
  data: T;
}