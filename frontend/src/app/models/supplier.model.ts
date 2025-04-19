import {Deserializable} from '../entities/deserializable.entity';

export class Supplier implements Deserializable {
  id?: number;
  name?: string;
  ruc?: string;
  address?: string;
  phone?: string;
  email?: string;
  description?: string;
  created_by?: number;
  updated_by?: number;

  constructor(name: string, ruc: string, address: string, phone: string, email?: string, description?: string, created_by?: number) {
    this.name = name;
    this.ruc = ruc;
    this.address = address;
    this.phone = phone;
    this.email = email;
    this.description = description;
    this.created_by = created_by;
  }

  deserializable(input: any): this {
    Object.assign(this, input);
    return this;
  }
}
