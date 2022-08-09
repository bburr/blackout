type DateTime = string;

export type Nullable<T> = T | null;

export interface User {
  uuid: number;
  name: string;
  created_at: DateTime;
  updated_at: DateTime;
}

export type InertiaSharedProps<T = {}> = T & {
  jetstream: {
    flash: any;
  };
  user: User;
  errorBags: any;
  errors: any;
};
