import { Component } from '@angular/core';
import { Router } from '@angular/router';
import { ApiService } from 'src/app/global/services/api.service';
import { API_response, Product } from 'src/app/global/types';
import { routes } from 'src/app/app-routing.module';
import { UserState } from 'src/app/global/state/user-state';
import { NgForm } from '@angular/forms';
import { ProductState } from 'src/app/global/state/product-state';

@Component({
    selector: 'app-products-add',
    templateUrl: './products-add.component.html',
    styleUrls: ['./products-add.component.scss']
})

export class ProductsAddComponent {

    all_products_route: string = routes.find( element => element.title === 'ViewProducts' ).path;

    constructor( private api : ApiService,
                 private user_state: UserState,
                 private product_state: ProductState
               ) 
    {}

    addProduct( input: Product, form: NgForm ) {

        const product: Product = {
            name: input.name,
            category: input.category,
            quantity: input.quantity,
            user_id: this.user_state.current_user.id,
        }

        //call types.ts service and pass endpoint and object
        this.api.post( 'products/create', product ).subscribe( 
            ( response: API_response ) => {
                
                //thow error if not successfull
                if ( response.success !== true )
                {
                    alert( response.message );
                    return
                }
                
                //temp
                alert( 'Product has been successfully added' );

                const new_product: Product = {
                    id: Number( response.message ),
                    category: input.category,
                    name: input.name,
                    quantity: input.quantity,
                }

                this.product_state.addProduct( new_product );

                form.reset();

            }
        );

    }
}