export interface User {
    email: string;
    password?: string;
    name?: string;
    role?: string;
    id?: number;
}

export interface TokenPayload extends User {
    exp?: number;   
}

export interface Product {
    category: string;
    name: string;
    quantity: number;
    user_id?: number;
    id?: number;
}

export interface API_response {
    success: boolean;
    message: string;
    token?: string;
    data?: Array<any>;
}
